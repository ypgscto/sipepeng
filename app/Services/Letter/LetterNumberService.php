<?php

namespace App\Services\Letter;

use App\Models\Letter\Letter;
use App\Models\Letter\LetterNumberSequence;
use Illuminate\Support\Facades\DB;

class LetterNumberService
{
    public function assignOfficialNumber(Letter $letter): string
    {
        $prefix = $letter->letter_prefix_snapshot
            ?? $letter->letterType?->letter_prefix
            ?? 'LPPM/SRT';
        $year = (int) $letter->letter_date?->format('Y') ?? (int) now()->format('Y');
        $pattern = $letter->letterType?->number_format_pattern
            ?? config('sipepeng_letters.default_number_pattern', '{prefix}/{seq:04d}/{year}');

        $seq = DB::transaction(function () use ($prefix, $year): int {
            $record = LetterNumberSequence::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    ['letter_prefix' => $prefix, 'year' => $year],
                    ['last_sequence' => 0],
                );
            $record->increment('last_sequence');

            return $record->fresh()->last_sequence;
        });

        return $this->formatNumber($pattern, $prefix, $seq, $year, $letter->letter_date);
    }

    protected function formatNumber(string $pattern, string $prefix, int $seq, int $year, $date): string
    {
        $monthRoman = $this->monthToRoman((int) ($date?->format('n') ?? now()->format('n')));

        $replacements = [
            '{prefix}' => $prefix,
            '{year}' => (string) $year,
            '{month_roman}' => $monthRoman,
            '{seq:04d}' => str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
            '{seq:03d}' => str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            '{seq}' => (string) $seq,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $pattern);
    }

    protected function monthToRoman(int $month): string
    {
        $map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];

        return $map[$month] ?? 'I';
    }
}
