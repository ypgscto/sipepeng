<?php

namespace App\Providers;

use App\Services\BrandingService;
use App\Services\Notification\NotificationService;
use App\Services\SidebarMenu;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureSessionForRequestRoot();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSessionForRequestRoot();

        if (! $this->app->runningInConsole()) {
            $request = request();
            if ($request && $this->app->environment('local')) {
                URL::forceRootUrl(rtrim($request->root(), '/'));
            }
        }

        View::composer('*', function ($view): void {
            $view->with('sipengBranding', app(BrandingService::class)->all());
        });

        View::composer([
            'layouts.dashboard',
            'layouts.partials.sidebar',
            'layouts.partials.sidebar-nav',
            'layouts.partials.topbar',
        ], function ($view): void {
            $sidebar = app(SidebarMenu::class);
            $view->with('sidebarMenu', $sidebar->build());
            $view->with('sidebarGroups', $sidebar->buildGroups());

            if (auth()->check()) {
                $service = app(NotificationService::class);
                $view->with('unreadNotificationCount', $service->unreadCount(auth()->user()));
                $view->with('recentNotifications', $service->recent(auth()->user(), 5));
            }
        });
    }

    protected function configureSessionForRequestRoot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = request();
        if (! $request) {
            return;
        }

        $basePath = parse_url($request->root(), PHP_URL_PATH) ?: '';
        if ($basePath !== '' && $basePath !== '/') {
            config(['session.path' => rtrim($basePath, '/').'/']);
        }
    }
}
