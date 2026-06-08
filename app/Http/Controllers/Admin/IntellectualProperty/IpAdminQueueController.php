<?php

namespace App\Http\Controllers\Admin\IntellectualProperty;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntellectualProperty\StoreIpVerificationRequest;
use App\Models\IntellectualProperty\IpRegistration;
use App\Services\IntellectualProperty\IpWorkflowService;
use App\Support\IntellectualProperty\IpPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IpAdminQueueController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(IpPermissions::canVerify(auth()->user()), 403);

        return view('admin.hki.queues.admin-verification', [
            'records' => IpRegistration::query()->where('status', 'admin_pending')->orderBy('submitted_at')->paginate(20),
        ]);
    }
}
