<?php

namespace App\Http\Controllers\Admin\Letter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Letter\StoreLetterApprovalRequest;
use App\Models\Letter\Letter;
use App\Services\Letter\LetterWorkflowService;
use Illuminate\Http\RedirectResponse;

class LetterApprovalController extends Controller
{
    public function store(StoreLetterApprovalRequest $request, Letter $letter, LetterWorkflowService $workflow): RedirectResponse
    {
        abort_unless($letter->status === 'pending_approval', 422);

        $workflow->recordApproval($letter, $request->validated('decision'), $request->validated('notes'));

        return redirect()->route('admin.letters.show', $letter)->with('success', 'Keputusan persetujuan dicatat.');
    }
}
