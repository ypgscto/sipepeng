<?php

namespace App\Http\Controllers;

use App\Services\Documents\ManualPdfService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManualController extends Controller
{
    /**
     * @return array<string, array<string, string>>
     */
    protected function modules(): array
    {
        return config('sipepeng_manual.modules', []);
    }

    /**
     * @return array{0: string, 1: array<string, string>}|null
     */
    protected function resolveModule(string $module): ?array
    {
        $modules = $this->modules();

        if (! isset($modules[$module]) || ! view()->exists("manual.modules.{$module}")) {
            return null;
        }

        return [$module, $modules[$module]];
    }

    /**
     * @return array{title: mixed, version: mixed, updated_at: mixed}
     */
    protected function manualMeta(): array
    {
        return [
            'title' => config('sipepeng_manual.title'),
            'version' => config('sipepeng_manual.version'),
            'updated_at' => config('sipepeng_manual.updated_at'),
        ];
    }

    public function index(): View
    {
        return view('manual.index', [
            'modules' => $this->modules(),
            'manualMeta' => $this->manualMeta(),
        ]);
    }

    public function show(string $module): View
    {
        $resolved = $this->resolveModule($module);

        if ($resolved === null) {
            abort(404);
        }

        [$slug, $meta] = $resolved;

        return view('manual.show', [
            'module' => $slug,
            'meta' => $meta,
            'modules' => $this->modules(),
            'manualMeta' => $this->manualMeta(),
        ]);
    }

    public function pdf(string $module, ManualPdfService $pdfService): Response|StreamedResponse
    {
        $resolved = $this->resolveModule($module);

        if ($resolved === null) {
            abort(404);
        }

        [$slug, $meta] = $resolved;

        $html = view('manual.pdf', [
            'module' => $slug,
            'meta' => $meta,
            'manualMeta' => $this->manualMeta(),
        ])->render();

        $filename = 'SOP-SiPepeng-'.str_replace('_', '-', $slug).'.pdf';

        return $pdfService->download($html, $filename);
    }
}
