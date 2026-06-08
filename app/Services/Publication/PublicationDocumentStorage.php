<?php

namespace App\Services\Publication;

use App\Support\Upload\SafeUploadFilename;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicationDocumentStorage
{
    public function store(UploadedFile $file, string $registrationNumber, string $field): array
    {
        $disk = (string) config('sipepeng_publication.storage_disk', 'local');
        $base = trim((string) config('sipepeng_publication.storage_path', 'lppm/publications'), '/');
        $allowed = config('sipepeng_publication.uploads.'.$field.'.mimes', ['pdf']);
        $safeNumber = SafeUploadFilename::sanitizeDirectorySegment($registrationNumber);
        $filename = SafeUploadFilename::build($file, $field, is_array($allowed) ? $allowed : ['pdf']);
        $path = $file->storeAs("{$base}/{$safeNumber}", $filename, $disk);

        return ['path' => $path, 'name' => basename($file->getClientOriginalName())];
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }
        $disk = (string) config('sipepeng_publication.storage_disk', 'local');
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function download(?string $path, ?string $name)
    {
        $disk = config('sipepeng_publication.storage_disk', 'local');
        if ($path === null || ! Storage::disk($disk)->exists($path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path, $name ?? basename($path));
    }
}
