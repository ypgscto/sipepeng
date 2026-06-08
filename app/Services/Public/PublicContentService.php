<?php

namespace App\Services\Public;

class PublicContentService
{
    /**
     * @return array{title: string, body: string}
     */
    public function about(): array
    {
        return config('sipepeng_public_dashboard.about', [
            'title' => 'Tentang SiPepeng',
            'body' => '',
        ]);
    }

    /**
     * @return list<string>
     */
    public function lppmFocus(): array
    {
        return config('sipepeng_public_dashboard.lppm_focus', []);
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    public function featuredThemes(): array
    {
        return config('sipepeng_public_dashboard.featured_themes', []);
    }

    /**
     * @return list<array<string, string>>
     */
    public function announcements(): array
    {
        return config('sipepeng_public_dashboard.announcements', []);
    }

    /**
     * @return list<array<string, string>>
     */
    public function calendarEvents(): array
    {
        return config('sipepeng_public_dashboard.calendar_events', []);
    }
}
