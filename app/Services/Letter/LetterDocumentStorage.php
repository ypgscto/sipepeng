<?php

namespace App\Services\Letter;

use App\Models\Letter\Letter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LetterDocumentStorage
{
    public function storeSignedScan(Letter $letter, UploadedFile $file): string
    {
        $disk = config('sipepeng_letters.storage_disk', 'local');
        $folder = config('sipepeng_letters.storage_path', 'lppm/letters').'/'.$letter->id;
        $ext = $file->getClientOriginalExtension() ?: 'pdf';
        $path = $folder.'/signed.'.$ext;

        Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));

        $letter->update([
            'file_signed_scan' => $path,
            'file_signed_scan_name' => $file->getClientOriginalName(),
            'signed_uploaded_at' => now(),
            'signed_uploaded_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return $path;
    }

    public function downloadPath(Letter $letter, string $field): ?string
    {
        $path = match ($field) {
            'file_pdf' => $letter->file_pdf,
            'file_signed_scan' => $letter->file_signed_scan,
            default => null,
        };

        if (! $path) {
            return null;
        }

        $disk = config('sipepeng_letters.storage_disk', 'local');
        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return $path;
    }
}
