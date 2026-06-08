<?php

namespace App\Services\Research;

use App\Support\Upload\SafeUploadFilename;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResearchDocumentStorage
{
    public function store(UploadedFile $file, string $proposalNumber, string $field): array
    {
        $disk = (string) config('sipepeng_research.storage_disk', 'local');
        $base = trim((string) config('sipepeng_research.storage_path', 'lppm/research'), '/');
        $allowed = config('sipepeng_research.uploads.'.$field.'.mimes', ['pdf']);
        $safeNumber = SafeUploadFilename::sanitizeDirectorySegment($proposalNumber);
        $filename = SafeUploadFilename::build($file, $field, is_array($allowed) ? $allowed : ['pdf']);
        $path = $file->storeAs("{$base}/{$safeNumber}", $filename, $disk);

        return [
            'path' => $path,
            'name' => basename($file->getClientOriginalName()),
        ];
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $disk = (string) config('sipepeng_research.storage_disk', 'local');
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function download(?string $path, ?string $name)
    {
        if ($path === null || ! Storage::disk(config('sipepeng_research.storage_disk', 'local'))->exists($path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return Storage::disk(config('sipepeng_research.storage_disk', 'local'))->download($path, $name ?? basename($path));
    }
}
