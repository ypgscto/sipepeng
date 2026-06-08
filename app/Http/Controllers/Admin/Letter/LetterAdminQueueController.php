<?php

namespace App\Http\Controllers\Admin\Letter;

use App\Http\Controllers\Controller;
use App\Models\Letter\Letter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterAdminQueueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $records = Letter::query()
            ->where('status', 'pending_approval')
            ->with(['letterType', 'creator'])
            ->orderBy('submitted_at')
            ->paginate(20);

        return view('admin.letters.queues.approval', [
            'records' => $records,
        ]);
    }
}
