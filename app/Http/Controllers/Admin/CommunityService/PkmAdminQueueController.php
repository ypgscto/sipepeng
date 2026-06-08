<?php

namespace App\Http\Controllers\Admin\CommunityService;

use App\Http\Controllers\Controller;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Support\CommunityService\PkmPermissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PkmAdminQueueController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless(PkmPermissions::canVerifyAdmin($request->user()), 403);

        $proposals = CommunityServiceProposal::query()
            ->where('status', 'admin_pending')
            ->orderBy('submitted_at')
            ->paginate(20);

        return view('admin.community-service.queues.admin-verification', [
            'proposals' => $proposals,
        ]);
    }
}
