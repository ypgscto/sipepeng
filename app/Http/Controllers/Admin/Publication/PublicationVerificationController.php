<?php

namespace App\Http\Controllers\Admin\Publication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publication\StorePublicationVerificationRequest;
use App\Models\Publication\Publication;
use App\Services\Publication\PublicationWorkflowService;
use App\Support\Publication\PublicationPermissions;
use Illuminate\Http\RedirectResponse;

class PublicationVerificationController extends Controller
{
    public function store(StorePublicationVerificationRequest $request, Publication $publication, PublicationWorkflowService $workflow): RedirectResponse
    {
        abort_unless(PublicationPermissions::canView($request->user(), $publication), 403);
        abort_unless($publication->status === 'admin_pending', 422);
        $v = $request->validated();
        $workflow->recordVerification($publication, $v['decision'], (bool) ($v['is_document_complete'] ?? false), $v['notes'] ?? null);

        return redirect()->route('admin.publications.show', $publication)->with('success', 'Verifikasi publikasi berhasil dicatat.');
    }
}
