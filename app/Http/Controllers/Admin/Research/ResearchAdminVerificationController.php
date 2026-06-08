<?php

namespace App\Http\Controllers\Admin\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\StoreAdminVerificationRequest;
use App\Models\Research\ResearchProposal;
use App\Services\Research\ResearchWorkflowService;
use App\Support\Research\ResearchPermissions;
use Illuminate\Http\RedirectResponse;

class ResearchAdminVerificationController extends Controller
{
    public function store(
        StoreAdminVerificationRequest $request,
        ResearchProposal $proposal,
        ResearchWorkflowService $workflow,
    ): RedirectResponse {
        abort_unless(ResearchPermissions::canView($request->user(), $proposal), 403);
        abort_unless($proposal->status === 'admin_pending', 422);

        $validated = $request->validated();

        $workflow->recordAdminVerification(
            $proposal,
            $validated['decision'],
            (bool) ($validated['is_document_complete'] ?? false),
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('admin.research.show', $proposal)
            ->with('success', 'Verifikasi administrasi berhasil dicatat.');
    }
}
