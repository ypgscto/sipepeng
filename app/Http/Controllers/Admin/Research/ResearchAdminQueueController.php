<?php

namespace App\Http\Controllers\Admin\Research;

use App\Http\Controllers\Controller;
use App\Models\Research\ResearchProposal;
use App\Support\Research\ResearchPermissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResearchAdminQueueController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless(ResearchPermissions::canVerifyAdmin($request->user()), 403);

        $proposals = ResearchProposal::query()
            ->where('status', 'admin_pending')
            ->orderBy('submitted_at')
            ->paginate(20);

        return view('admin.research.queues.admin-verification', [
            'proposals' => $proposals,
        ]);
    }
}
