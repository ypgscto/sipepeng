<?php

namespace App\Services\Siakad;

class SiakadReferenceFilter
{
    /**
     * @param  list<array<string, mixed>>  $records
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function apply(string $tab, array $records, array $filters): array
    {
        $q = strtolower(trim((string) ($filters['q'] ?? '')));

        return array_values(array_filter($records, function (array $row) use ($tab, $filters, $q): bool {
            if (! $this->matchesSearch($tab, $row, $q)) {
                return false;
            }

            return match ($tab) {
                'dosen' => $this->matchesDosen($row, $filters),
                'mahasiswa' => $this->matchesMahasiswa($row, $filters),
                'prodi' => true,
                'tahun_akademik', 'semester' => $this->matchesTahunAkademik($row, $filters),
                default => true,
            };
        }));
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function matchesSearch(string $tab, array $row, string $q): bool
    {
        if ($q === '') {
            return true;
        }

        $haystack = match ($tab) {
            'dosen' => [
                $row['siakad_id'] ?? '',
                $row['nama'] ?? '',
                $row['nidn'] ?? '',
                $row['nip'] ?? '',
                $row['email'] ?? '',
                $row['nama_prodi_homebase'] ?? '',
                $row['homebase_prodi_id'] ?? '',
            ],
            'mahasiswa' => [
                $row['nim'] ?? '',
                $row['nama'] ?? '',
                $row['email'] ?? '',
                $row['nama_prodi'] ?? '',
                $row['prodi_siakad_id'] ?? '',
                $row['status_mahasiswa_nama'] ?? '',
            ],
            'prodi' => [
                $row['kode_prodi'] ?? '',
                $row['nama_prodi'] ?? '',
                $row['siakad_id'] ?? '',
                $row['jenjang'] ?? '',
            ],
            'tahun_akademik', 'semester' => [
                $row['siakad_id'] ?? '',
                $row['nama_tahun_akademik'] ?? '',
                $row['semester'] ?? '',
            ],
            default => [],
        };

        foreach ($haystack as $part) {
            if (str_contains(strtolower((string) $part), $q)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $filters
     */
    protected function matchesDosen(array $row, array $filters): bool
    {
        $prodiId = trim((string) ($filters['prodi_id'] ?? ''));
        if ($prodiId !== '' && (string) ($row['homebase_prodi_id'] ?? '') !== $prodiId) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $filters
     */
    protected function matchesMahasiswa(array $row, array $filters): bool
    {
        $prodiId = trim((string) ($filters['prodi_id'] ?? ''));
        if ($prodiId !== '' && (string) ($row['prodi_siakad_id'] ?? '') !== $prodiId) {
            return false;
        }

        $angkatan = trim((string) ($filters['angkatan'] ?? ''));
        if ($angkatan !== '' && (string) ($row['angkatan'] ?? '') !== $angkatan) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $filters
     */
    protected function matchesTahunAkademik(array $row, array $filters): bool
    {
        $status = strtolower(trim((string) ($filters['status'] ?? '')));
        if (in_array($status, ['aktif', 'active', '1'], true) && ! ($row['is_active'] ?? false)) {
            return false;
        }

        $semester = strtolower(trim((string) ($filters['semester'] ?? '')));
        if ($semester !== '' && strtolower((string) ($row['semester'] ?? '')) !== $semester) {
            return false;
        }

        return true;
    }
}
