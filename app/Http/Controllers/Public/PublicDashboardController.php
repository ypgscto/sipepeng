<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Public\PublicContentService;
use App\Services\Public\PublicDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDashboardController extends Controller
{
    public function __construct(
        protected PublicDashboardService $dashboard,
        protected PublicContentService $content,
    ) {}

    public function landing(Request $request): View
    {
        $filter = $this->dashboard->filterFromRequest(
            $request->filled('year') ? (int) $request->input('year') : null,
        );

        return view('public.landing', [
            'filter' => $filter,
            'stats' => $this->dashboard->highlightStats($filter),
            'yearOptions' => $this->yearOptions(),
        ]);
    }

    public function dashboard(Request $request): View
    {
        $filter = $this->dashboard->filterFromRequest(
            $request->filled('year') ? (int) $request->input('year') : null,
        );

        $payload = $this->dashboard->payload($filter);

        return view('public.dashboard', array_merge($payload, [
            'yearOptions' => $this->yearOptions(),
            'about' => $this->content->about(),
            'lppmFocus' => $this->content->lppmFocus(),
            'featuredThemes' => $this->content->featuredThemes(),
            'announcements' => $this->content->announcements(),
            'calendarEvents' => $this->content->calendarEvents(),
        ]));
    }

    /**
     * @return list<int>
     */
    protected function yearOptions(): array
    {
        $current = (int) now()->format('Y');
        $back = (int) config('sipepeng_public_dashboard.allowed_years_back', 10);

        return range($current, $current - $back);
    }
}
