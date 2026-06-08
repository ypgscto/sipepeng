<?php

namespace App\Http\Controllers\Admin\ResearchEthics;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResearchEthics\AssignEthicsReviewerRequest;
use App\Http\Requests\ResearchEthics\StoreEthicsDecisionRequest;
use App\Models\ResearchEthics\ResearchEthicsApplication;
use App\Services\ResearchEthics\EthicsWorkflowService;
use App\Support\ResearchEthics\EthicsPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResearchEthicsReviewController extends Controller
{
    public function queue(): View
    {
        abort_unless(auth()->user()->hasAnyRole(config('sipepeng_ethics.manage_roles', [])), 403);

        return view('admin.research-ethics.queues.committee-review', [
            'records' => ResearchEthicsApplication::query()->where('status', 'committee_review')->orderBy('submitted_at')->paginate(20),
        ]);
    }

    public function assign(AssignEthicsReviewerRequest $request, ResearchEthicsApplication $ethicsApplication, EthicsWorkflowService $workflow): RedirectResponse
    {
        abort_unless(EthicsPermissions::canView($request->user(), $ethicsApplication), 403);
        $workflow->assignReviewer($ethicsApplication, (int) $request->validated('reviewer_id'));

        return redirect()->route('admin.research-ethics.show', $ethicsApplication)->with('success', 'Reviewer etik ditugaskan.');
    }

    public function decide(StoreEthicsDecisionRequest $request, ResearchEthicsApplication $ethicsApplication, EthicsWorkflowService $workflow): RedirectResponse
    {
        abort_unless(EthicsPermissions::canView($request->user(), $ethicsApplication), 403);
        abort_unless($ethicsApplication->status === 'committee_review', 422);
        $v = $request->validated();
        $workflow->decide($ethicsApplication, $v['decision'], $v['notes'] ?? null, $v['valid_until'] ?? null);

        return redirect()->route('admin.research-ethics.show', $ethicsApplication)->with('success', 'Keputusan etik berhasil disimpan.');
    }
}
