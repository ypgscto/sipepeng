<?php

namespace App\Services\Research;

use App\Services\Siakad\SiakadReferenceService;
use App\Support\Siakad\SiakadResource;

class ResearchSiakadLookup
{
    public function __construct(
        protected SiakadReferenceService $reference,
    ) {}

    /**
     * @return array{options: list<array{value: string, label: string}>, records: list<array<string, mixed>>}
     */
    public function prodi(): array
    {
        $data = $this->reference->forTab('prodi');

        return [
            'options' => $data['prodi_options'] ?? [],
            'records' => $data['records'],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function tahunAkademik(): array
    {
        $data = $this->reference->forTab('tahun_akademik');
        $options = [];
        foreach ($data['records'] as $row) {
            $id = (string) ($row['siakad_id'] ?? '');
            if ($id === '') {
                continue;
            }
            $options[] = [
                'value' => $id,
                'label' => (string) ($row['nama_tahun_akademik'] ?? $row['tahun'] ?? $id),
            ];
        }

        return $options;
    }

    /**
     * @return list<array{value: string, label: string, tahun_akademik_id: string}>
     */
    public function semester(): array
    {
        $data = $this->reference->forTab('semester');
        $options = [];
        foreach ($data['records'] as $row) {
            $id = (string) ($row['siakad_id'] ?? '');
            if ($id === '') {
                continue;
            }
            $semester = (string) ($row['semester'] ?? '');
            $taName = (string) ($row['nama_tahun_akademik'] ?? '');
            $options[] = [
                'value' => $id,
                'label' => trim($taName.' — '.ucfirst($semester)),
                'tahun_akademik_id' => $id,
                'tahun_akademik_nama' => $taName,
                'semester_nama' => ucfirst($semester),
            ];
        }

        return $options;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findProdi(string $id): ?array
    {
        return $this->findInTab('prodi', $id, ['siakad_id', 'kode_prodi']);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findTahunAkademik(string $id): ?array
    {
        return $this->findInTab('tahun_akademik', $id, ['siakad_id']);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findSemester(string $id): ?array
    {
        return $this->findInTab('semester', $id, ['siakad_id']);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDosen(string $id): ?array
    {
        return $this->findInTab('dosen', $id, ['siakad_id']);
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>|null
     */
    protected function findInTab(string $tab, string $id, array $keys): ?array
    {
        $data = $this->reference->forTab($tab);
        foreach ($data['records'] as $row) {
            foreach ($keys as $key) {
                if ((string) ($row[$key] ?? '') === $id) {
                    return $row;
                }
            }
        }

        return null;
    }

    public function prodiName(string $id): string
    {
        $row = $this->findProdi($id);

        return (string) ($row['nama_prodi'] ?? $id);
    }

    public function tahunAkademikName(string $id): string
    {
        $row = $this->findTahunAkademik($id);

        return (string) ($row['nama_tahun_akademik'] ?? $row['tahun'] ?? $id);
    }

    public function semesterLabel(string $id): string
    {
        $row = $this->findSemester($id);
        if ($row === null) {
            return $id;
        }

        $ta = (string) ($row['nama_tahun_akademik'] ?? '');
        $sem = (string) ($row['semester'] ?? '');

        return trim($ta.' — '.ucfirst($sem));
    }
}
