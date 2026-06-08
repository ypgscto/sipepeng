<?php

namespace App\Services\Letter;

use App\Models\Letter\Letter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LetterPdfService
{
    public function resolveView(Letter $letter): string
    {
        $template = $letter->documentTemplate;
        if ($template?->blade_view) {
            return $template->blade_view;
        }

        $code = $letter->letterType?->code;
        $map = [
            'surat_tugas_penelitian' => 'admin.letters.templates.surat-tugas-penelitian',
            'surat_tugas_pkm' => 'admin.letters.templates.surat-tugas-pkm',
            'surat_izin_penelitian' => 'admin.letters.templates.surat-izin-penelitian',
            'surat_izin_pkm' => 'admin.letters.templates.surat-izin-pkm',
            'surat_permohonan_data' => 'admin.letters.templates.surat-permohonan-data',
            'surat_pengantar_mitra' => 'admin.letters.templates.surat-pengantar-mitra',
            'surat_undangan_reviewer' => 'admin.letters.templates.surat-undangan-reviewer',
            'surat_undangan_seminar' => 'admin.letters.templates.surat-undangan-seminar',
            'surat_keterangan_selesai_penelitian' => 'admin.letters.templates.surat-keterangan-selesai-penelitian',
            'surat_keterangan_selesai_pkm' => 'admin.letters.templates.surat-keterangan-selesai-pkm',
            'surat_keterangan_luaran' => 'admin.letters.templates.surat-keterangan-luaran',
            'surat_tugas' => 'admin.letters.templates.surat-tugas-penelitian',
        ];

        return $map[$code] ?? 'admin.letters.templates.generic';
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(Letter $letter, bool $watermark = false): array
    {
        $letter->loadMissing(['letterType', 'recipients', 'researchProposal', 'communityServiceProposal', 'partner', 'reviewer.user']);

        return [
            'letter' => $letter,
            'institution' => config('sipepeng_letters.institution_name'),
            'watermark' => $watermark ? ($letter->isIssued() ? null : 'DRAFT') : null,
            'displayNumber' => $letter->displayNumber(),
            'letterDate' => $letter->letter_date?->translatedFormat('d F Y'),
            'placeOfIssue' => $letter->place_of_issue ?? config('sipepeng_letters.place_of_issue'),
        ];
    }

    public function generateAndStore(Letter $letter, bool $watermark = false): string
    {
        $view = $this->resolveView($letter);
        $data = $this->buildViewData($letter, $watermark);
        $pdf = Pdf::loadView($view, $data)->setPaper('A4');

        $disk = config('sipepeng_letters.storage_disk', 'local');
        $folder = config('sipepeng_letters.storage_path', 'lppm/letters').'/'.$letter->id;
        $filename = 'surat-'.($letter->letter_number ?? $letter->internal_number).'.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename) ?? 'surat.pdf';
        $path = $folder.'/'.$filename;

        Storage::disk($disk)->put($path, $pdf->output());

        $letter->update([
            'file_pdf' => $path,
            'file_pdf_name' => $filename,
            'updated_by' => auth()->id(),
        ]);

        return $path;
    }

    public function stream(Letter $letter, bool $watermark = false)
    {
        $view = $this->resolveView($letter);
        $data = $this->buildViewData($letter, $watermark);

        return Pdf::loadView($view, $data)->setPaper('A4')->stream('surat.pdf');
    }
}
