<?php

namespace App\Http\Controllers\Admin\Research;

use App\Http\Controllers\Controller;
use App\Http\Requests\Research\AssignReviewerRequest;
use App\Http\Requests\Research\StoreDecisionRequest;
use App\Http\Requests\Research\SubmitReviewRequest;
use App\Models\Research\ResearchProposal;
use App\Models\Research\ResearchReview;
use App\Services\Research\ResearchWorkflowService;
use App\Support\Research\ResearchPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResearchReviewController extends Controller
{
    public function queue(): View
    {
        $user = auth()->user();
        $query = ResearchProposal::query()
            ->visibleTo($user)
            ->whereIn('status', ['admin_pending', 'review_assigned', 'review_completed'])
            ->orderByDesc('submitted_at');

        if ($user->hasRole('reviewer') && ! $user->hasAnyRole(config('sipepeng_research.view_all_roles', []))) {
            $query->whereHas('reviews', fn ($q) => $q->whereHas('reviewer', fn ($r) => $r->where('user_id', $user->id)));
        }

        return view('admin.research.queues.review', [
            'proposals' => $query->paginate(20),
        ]);
    }

    public function assign(
        AssignReviewerRequest $request,
        ResearchProposal $proposal,
        ResearchWorkflowService $workflow,
    ): RedirectResponse {
        abort_unless(ResearchPermissions::canView($request->user(), $proposal), 403);

        $workflow->assignReviewer($proposal, (int) $request->validated('reviewer_id'));

        return redirect()
            ->route('admin.research.show', $proposal)
            ->with('success', 'Reviewer berhasil ditugaskan.');
    }

    public function submit(
        SubmitReviewRequest $request,
        ResearchProposal $proposal,
        ResearchWorkflowService $workflow,
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
            ->route('admin.research.show', $proposal)
            ->with('success', 'Review berhasil disubmit.');
    }

    public function decide(
        StoreDecisionRequest $request,
        ResearchProposal $proposal,
        ResearchWorkflowService $workflow,
    ): RedirectResponse {
        abort_unless(ResearchPermissions::canView($request->user(), $proposal), 403);
        abort_unless($proposal->status === 'review_completed', 422);

        $workflow->decide($proposal, $request->validated('decision'), $request->validated('notes'));

        return redirect()
            ->route('admin.research.show', $proposal)
            ->with('success', 'Keputusan penetapan berhasil disimpan.');
    }
}
