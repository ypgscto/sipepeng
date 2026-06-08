<?php

namespace App\Http\Controllers\Admin\CommunityService;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityService\AssignPkmReviewerRequest;
use App\Http\Requests\CommunityService\StorePkmDecisionRequest;
use App\Http\Requests\CommunityService\SubmitPkmReviewRequest;
use App\Models\CommunityService\CommunityServiceProposal;
use App\Services\CommunityService\PkmWorkflowService;
use App\Support\CommunityService\PkmPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PkmReviewController extends Controller
{
    public function queue(): View
    {
        $user = auth()->user();
        $query = CommunityServiceProposal::query()
            ->visibleTo($user)
            ->whereIn('status', ['admin_pending', 'review_assigned', 'review_completed'])
            ->orderByDesc('submitted_at');

        if ($user->hasRole('reviewer') && ! $user->hasAnyRole(config('sipepeng_community_service.view_all_roles', []))) {
            $query->whereHas('reviews', fn ($q) => $q->whereHas('reviewer', fn ($r) => $r->where('user_id', $user->id)));
        }

        return view('admin.community-service.queues.review', [
            'proposals' => $query->paginate(20),
        ]);
    }

    public function assign(
        AssignPkmReviewerRequest $request,
        CommunityServiceProposal $proposal,
        PkmWorkflowService $workflow,
    ): RedirectResponse {
        abort_unless(PkmPermissions::canView($request->user(), $proposal), 403);

        $workflow->assignReviewer($proposal, (int) $request->validated('reviewer_id'));

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Reviewer berhasil ditugaskan.');
    }

    public function submit(
        SubmitPkmReviewRequest $request,
        CommunityServiceProposal $proposal,
        PkmWorkflowService $workflow,
    ): RedirectResponse {
        $review = $proposal->reviews()
            ->where('status', 'assigned')
            ->whereHas('reviewer', fn ($q) => $q->where('user_id', $request->user()->id))
            ->firstOrFail();

        $validated = $request->validated();

        $workflow->submitReview(
            $review,
            $validated['recommendation'],
            (float) $validated['overall_score'],
            $validated['summary'] ?? null,
        );

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Review berhasil disubmit.');
    }

    public function decide(
        StorePkmDecisionRequest $request,
        CommunityServiceProposal $proposal,
        PkmWorkflowService $workflow,
    ): RedirectResponse {
        abort_unless(PkmPermissions::canView($request->user(), $proposal), 403);
        abort_unless($proposal->status === 'review_completed', 422);

        $workflow->decide($proposal, $request->validated('decision'), $request->validated('notes'));

        return redirect()
            ->route('admin.community-service.show', $proposal)
            ->with('success', 'Keputusan penetapan berhasil disimpan.');
    }
}
