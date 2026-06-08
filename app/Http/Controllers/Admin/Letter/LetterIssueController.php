<?php

namespace App\Http\Controllers\Admin\Letter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Letter\IssueLetterRequest;
use App\Models\Letter\Letter;
use App\Services\Letter\LetterWorkflowService;
use Illuminate\Http\RedirectResponse;

class LetterIssueController extends Controller
{
    public function store(IssueLetterRequest $request, Letter $letter, LetterWorkflowService $workflow): RedirectResponse
    {
        $workflow->issue($letter);

        return redirect()->route('admin.letters.show', $letter->fresh())->with('success', 'Surat berhasil diterbitkan dengan nomor resmi.');
    }
}
