<?php

namespace App\Support\Ui;

final class StatusBadgeVariant
{
    public static function resolve(?string $status): string
    {
        if ($status === null || $status === '') {
            return config('sipeng_status_variants.default', 'neutral');
        }

        $map = config('sipeng_status_variants.map', []);

        if (isset($map[$status])) {
            return (string) $map[$status];
        }

        if (str_contains($status, 'reject')) {
            return 'rejected';
        }

        if (str_contains($status, 'approv') || str_contains($status, 'verif')) {
            return 'approved';
        }

        if (str_contains($status, 'review') || str_contains($status, 'pending')) {
            return 'review';
        }

        if (str_contains($status, 'revision')) {
            return 'revision';
        }

        if (str_contains($status, 'draft')) {
            return 'draft';
        }

        return config('sipeng_status_variants.default', 'neutral');
    }

    public static function classes(string $variant): string
    {
        return match ($variant) {
            'draft' => 'bg-slate-100 text-slate-700 border border-slate-200',
            'submitted' => 'bg-sky-50 text-sky-800 border border-sky-200',
            'review' => 'bg-amber-50 text-amber-900 border border-amber-200',
            'revision' => 'bg-orange-50 text-orange-900 border border-orange-200',
            'approved' => 'bg-emerald-50 text-emerald-800 border border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-800 border border-rose-200',
            'inactive' => 'bg-slate-100 text-slate-600 border border-slate-200',
            default => 'bg-teal-50 text-teal-800 border border-teal-200',
        };
    }
}
