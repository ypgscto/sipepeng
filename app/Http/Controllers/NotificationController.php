<?php

namespace App\Http\Controllers;

use App\Models\Notification\LppmNotification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'all');

        $query = LppmNotification::query()
            ->forUser($request->user()->id)
            ->inbox();

        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(20)->withQueryString(),
            'filter' => $filter,
            'unreadCount' => $this->notifications->unreadCount($request->user()),
            'categories' => config('sipepeng_notifications.categories', []),
        ]);
    }

    public function markRead(Request $request, LppmNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $this->notifications->markRead($notification);

        if ($request->boolean('redirect') && $notification->action_url) {
            $target = $this->safeRedirectUrl($notification->action_url);

            return $target !== null
                ? redirect()->to($target)
                : back()->with('success', 'Notifikasi ditandai sudah dibaca.');
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $count = $this->notifications->markAllRead($request->user());

        return back()->with('success', "{$count} notifikasi ditandai sudah dibaca.");
    }

    public function dismiss(Request $request, LppmNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $this->notifications->dismiss($notification);

        return back()->with('success', 'Notifikasi diabaikan.');
    }

    protected function safeRedirectUrl(string $url): ?string
    {
        if (str_starts_with($url, '/')) {
            return $url;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && str_starts_with($url, $appUrl.'/')) {
            return str_replace($appUrl, '', $url) ?: '/';
        }

        return null;
    }
}
