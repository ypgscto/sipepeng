<?php

namespace App\Services\Documents;

use Barryvdh\DomPDF\Facade\Pdf;

class ManualPdfService
{
    public function download(string $html, string $filename)
    {
        return Pdf::loadHTML($html)->setPaper('a4')->download($filename);
    }
}
