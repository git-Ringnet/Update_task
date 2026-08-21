<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $uploaded = [];

        $files = $request->file('files');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            // Generate a unique filename to avoid collisions
            $ext = $file->getClientOriginalExtension() ?: 'bin';
            $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $uniqueName = $safeName . '_' . Str::random(8) . '.' . $ext;

            $path = $file->storeAs('attachments', $uniqueName, 'public');

            $attachment = Attachment::create([
                'uploaded_by' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $uploaded[] = $attachment;
        }

        return response()->json($uploaded, 201);
    }
}
