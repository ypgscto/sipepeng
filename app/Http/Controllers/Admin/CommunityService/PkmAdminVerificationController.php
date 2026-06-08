<?php

namespace App\Http\Controllers\Admin\CommunityService;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityService\StorePkmAdminVerificationRequest;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Services\CommunityService\PkmWorkflowService;
use App\Support\CommunityService\PkmPermissions;
use Illuminate\Http\RedirectResponse;

class PkmAdminVerificationController extends Controller
{
    public function store(
        StorePkmAdminVerificationRequest $request,
        CommunityServiceProposal $proposal,
        PkmWorkflowService $workflow,
    ): RedirectResponse {
        abort_unless(PkmPermissions::canView($request->user(), $proposal), 403);
        abort_unless($proposal->status === 'admin_pending', 422);

        $validated = $request->validated();

        $workflow->recordAdminVerification(
            $proposal,
            $validated['decision'],
            (bool) ($validated['is_document_complete'] ?? false),
            (bool) ($validated['is_partner_verified'] ?? false),
            $validated['notes'] ?? null,
        );

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Verifikasi administrasi berhasil dicatat.');
    }
}
