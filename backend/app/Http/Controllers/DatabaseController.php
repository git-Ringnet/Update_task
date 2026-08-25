<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function exportSql()
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return $this->exportSqlite($connection);
        }

        if ($driver === 'mysql') {
            return $this->exportMysql($connection);
        }

        return response()->json(['message' => "Hệ quản trị cơ sở dữ liệu '{$driver}' chưa được hỗ trợ xuất."], 400);
    }

    private function exportSqlite($connection)
    {
        $tables = [];
        $result = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        foreach ($result as $row) {
            $tables[] = $row->name;
        }

        $sql = "-- Database SQLite Export\n";
        $sql .= "-- Generated at: " . now()->toDateTimeString() . "\n\n";
        $sql .= "PRAGMA foreign_keys = OFF;\n\n";

        foreach ($tables as $table) {
            $createStatement = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
            if (empty($createStatement)) {
                continue;
            }
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStatement[0]->sql . ";\n\n";

            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $rowArray = (array)$row;
                if (empty($rowArray)) {
                    continue;
                }
                $columns = array_keys($rowArray);
                $escapedValues = array_map(function ($value) use ($connection) {
                    if (is_null($value)) {
                        return 'NULL';
                    }
                    return $connection->getPdo()->quote($value);
                }, array_values($rowArray));

                $sql .= "INSERT INTO `{$table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "PRAGMA foreign_keys = ON;\n";

        $filename = 'backup_sqlite_' . date('Ymd_His') . '.sql';

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportMysql($connection)
    {
        $tables = [];
        $result = DB::select('SHOW TABLES');
        $dbNameKey = 'Tables_in_' . $connection->getDatabaseName();

        foreach ($result as $row) {
            $tables[] = $row->$dbNameKey;
        }

        $sql = "-- Database MySQL Export\n";
        $sql .= "-- Generated at: " . now()->toDateTimeString() . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $createStatement = DB::select("SHOW CREATE TABLE `{$table}`");
            $createStatementArray = (array)$createStatement[0];
            $createTableSql = array_values($createStatementArray)[1];
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTableSql . ";\n\n";

            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sql .= "LOCK TABLES `{$table}` WRITE;\n";
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $columns = array_keys($rowArray);
                    $escapedValues = array_map(function ($value) use ($connection) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return $connection->getPdo()->quote($value);
                    }, array_values($rowArray));

                    $sql .= "INSERT INTO `{$table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
                }
                $sql .= "UNLOCK TABLES;\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_' . $connection->getDatabaseName() . '_' . date('Ymd_His') . '.sql';

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
