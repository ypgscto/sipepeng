<?php

namespace App\Http\Controllers\Admin\Publication;

use App\Http\Controllers\Controller;
use App\Models\Publication\Publication;
use App\Support\Publication\PublicationPermissions;
use Illuminate\View\View;

class PublicationAdminQueueController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(PublicationPermissions::canVerify(auth()->user()), 403);

        return view('admin.publications.queues.admin-verification', [
            'records' => Publication::query()->where('status', 'admin_pending')->orderBy('submitted_at')->paginate(20),
        ]);
    }
}
