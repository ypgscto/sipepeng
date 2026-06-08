<?php

namespace App\Http\Controllers\Admin\IntellectualProperty;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualProperty\StoreIpVerificationRequest;
use App\Models\IntellectualProperty\IpRegistration;
use App\Services\IntellectualProperty\IpWorkflowService;
use App\Support\IntellectualProperty\IpPermissions;
use Illuminate\Http\RedirectResponse;

class IpVerificationController extends Controller
{
    public function store(StoreIpVerificationRequest $request, IpRegistration $ipRegistration, IpWorkflowService $workflow): RedirectResponse
    {
        abort_unless(IpPermissions::canView($request->user(), $ipRegistration), 403);
        abort_unless($ipRegistration->status === 'admin_pending', 422);
        $v = $request->validated();
        $workflow->recordVerification($ipRegistration, $v['decision'], (bool) ($v['is_document_complete'] ?? false), $v['notes'] ?? null);

        return redirect()->route('admin.hki.show', $ipRegistration)->with('success', 'Verifikasi HKI berhasil dicatat.');
    }
}
