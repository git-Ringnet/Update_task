<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AttachmentController extends Controller
{
    /**
     * Upload one or more files and return their metadata + URLs.
     * Files are stored in storage/app/public/attachments/ and served via the storage symlink.
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:51200', // 50 MB max per file
        ]);

        $files = $request->file('files');
        if (!is_array($files)) {
            $files = [$files];
        }

        $disk = Storage::disk('public');
        $storedPaths = [];

        try {
            $uploaded = DB::transaction(function () use ($files, $disk, &$storedPaths) {
                $uploaded = [];

                foreach ($files as $file) {
                    // Generate a unique filename to avoid collisions.
                    $ext = $file->getClientOriginalExtension() ?: 'bin';
                    $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
                    $uniqueName = $safeName . '_' . Str::random(8) . '.' . $ext;

                    $path = $disk->putFileAs('attachments', $file, $uniqueName);

                    // The local filesystem returns false instead of throwing when
                    // "throw" is disabled. Never persist an empty path as /storage.
                    if (!is_string($path) || $path === '' || !$disk->exists($path)) {
                        throw new RuntimeException(
                            'The public disk could not write the uploaded file. ' .
                            'Check storage/app/public permissions and available disk space.'
                        );
                    }

                    $storedPaths[] = $path;
                    $uploaded[] = Attachment::create([
                        'uploaded_by' => auth()->id(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }

                return $uploaded;
            });
        } catch (Throwable $exception) {
            // A database failure after writing a file must not leave orphan files.
            if ($storedPaths !== []) {
                $disk->delete($storedPaths);
            }

            report($exception);

            $response = [
                'message' => 'Không thể lưu tệp lên máy chủ. Vui lòng kiểm tra quyền ghi của thư mục storage/app/public.',
                'error_code' => 'ATTACHMENT_STORAGE_FAILED',
            ];

            if (config('app.debug')) {
                $response['detail'] = $exception->getMessage();
            }

            return response()->json($response, 500);
        }

        return response()->json($uploaded, 201);
    }
}
