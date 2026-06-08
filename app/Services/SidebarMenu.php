<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class SidebarMenu
{
    /**
     * @return list<array<string, mixed>>
     */
    public function buildGroups(): array
    {
        $groups = [];

        foreach (config('sipeng_sidebar.groups', []) as $group) {
            $items = [];

            foreach ($group['items'] ?? [] as $item) {
                if (! $this->userCanSeeItem($item)) {
                    continue;
                }

                $items[] = $this->buildLink($item);
            }

            if ($items !== []) {
                $groups[] = [
                    'label' => (string) ($group['label'] ?? ''),
                    'items' => $items,
                ];
            }
        }

        return $groups;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function build(): array
    {
        return collect($this->buildGroups())
            ->flatMap(fn (array $group) => $group['items'])
            ->values()
            ->all();
    }

    public function labelForRoute(?string $routeName): string
    {
        if ($routeName === null) {
            return 'Modul';
        }

        foreach (config('sipeng_sidebar.groups', []) as $group) {
            foreach ($group['items'] ?? [] as $item) {
                if (($item['route'] ?? '') === $routeName) {
                    return (string) ($item['label'] ?? 'Modul');
                }
            }
        }

        foreach (config('sipeng_sidebar.items', []) as $item) {
            if (($item['route'] ?? '') === $routeName) {
                return (string) ($item['label'] ?? 'Modul');
            }
        }

        return 'Modul';
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function userCanSeeItem(array $item): bool
    {
        if (($item['hidden'] ?? false) === true) {
            return false;
        }

        $roles = $item['roles'] ?? null;

        if ($roles === null) {
            return true;
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasAnyRole($roles);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function buildLink(array $item): array
    {
        $routeName = (string) ($item['route'] ?? '');

        return [
            'type' => 'link',
            'label' => (string) ($item['label'] ?? ''),
            'icon' => (string) ($item['icon'] ?? 'link'),
            'url' => $routeName !== '' && Route::has($routeName)
                ? route($routeName)
                : '#',
            'active' => $this->isActive($item),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isActive(array $item): bool
    {
        $patterns = $item['active_routes'] ?? [];

        if ($patterns === [] && isset($item['route'])) {
            $patterns = [(string) $item['route']];
        }

        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }
}
