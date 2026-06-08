<?php

namespace App\Support\Upload;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SafeUploadFilename
{
    /**
     * @param  list<string>  $allowedExtensions
     */
    public static function build(
        UploadedFile $file,
        string $prefix,
        array $allowedExtensions = ['pdf'],
    ): string {
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension()));
        if (! in_array($extension, $allowedExtensions, true)) {
            $extension = $allowedExtensions[0];
        }

        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        if ($safeName === '') {
            $safeName = 'file';
        }

        return $prefix.'_'.now()->format('YmdHis').'_'.$safeName.'.'.$extension;
    }

    public static function sanitizeDirectorySegment(string $segment): string
    {
        return Str::slug($segment, '_');
    }
}
