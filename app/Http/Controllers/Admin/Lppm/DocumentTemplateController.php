<?php

namespace App\Http\Controllers\Admin\Lppm;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentTemplateController extends LppmMasterController
{
    protected function entityKey(): string
    {
        return 'document-templates';
    }

    public function download(int|string|Model $record): StreamedResponse
    {
        $record = $this->resolveRecord($record);

        if (! Storage::disk('local')->exists($record->file_path)) {
            abort(404, 'Berkas template tidak ditemukan.');
        }

        return Storage::disk('local')->download(
            $record->file_path,
            $record->file_name,
            ['Content-Type' => $record->mime_type],
        );
    }
}
