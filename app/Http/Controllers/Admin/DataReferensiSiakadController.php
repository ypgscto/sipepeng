<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\Siakad\SiakadReferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataReferensiSiakadController extends Controller
{
    public function index(Request $request, SiakadReferenceService $reference): View
    {
        $tab = (string) $request->query('tab', 'prodi');
        $filters = $this->extractFilters($request);

        $data = $reference->forTab($tab, $filters);

        return view('admin.siakad-reference.index', $data);
    }

    public function refresh(Request $request, SiakadReferenceService $reference, ActivityLogger $logger): RedirectResponse
    {
        $tab = (string) $request->input('tab', $request->query('tab', 'prodi'));
        $filters = $this->extractFilters($request);

        $result = $reference->refresh($tab);

        $logger->logAudit(
            'reference_refreshed',
            null,
            'Cache referensi SIAKAD diperbarui.',
            ['tab' => $tab, 'success' => $result['error'] === null],
            $request,
            logName: 'siakad_reference',
        );

        if ($result['error'] !== null) {
            return redirect()
                ->route('admin.siakad-reference.index', array_merge(['tab' => $result['tab']], $filters))
                ->with('error', $result['error']);
        }

        $label = $this->tabLabel($result['tab']);

        return redirect()
            ->route('admin.siakad-reference.index', array_merge(['tab' => $result['tab']], $filters))
            ->with('success', "Data {$label} berhasil diperbarui dari SIAKAD-API.");
    }

    /**
     * @return array<string, string>
     */
    protected function extractFilters(Request $request): array
    {
        return array_filter([
            'q' => $request->query('q'),
            'prodi_id' => $request->query('prodi_id'),
            'angkatan' => $request->query('angkatan'),
            'status' => $request->query('status'),
            'semester' => $request->query('semester'),
        ], fn ($value) => is_string($value) && trim($value) !== '');
    }

    protected function tabLabel(string $tab): string
    {
        return match ($tab) {
            'dosen' => 'Dosen',
            'mahasiswa' => 'Mahasiswa',
            'prodi' => 'Program Studi',
            'tahun_akademik' => 'Tahun Akademik',
            'semester' => 'Semester',
            default => 'referensi',
        };
    }
}
