<?php

namespace App\Services\CommunityService;

use App\Services\Siakad\SiakadReferenceService;

class PkmSiakadLookup
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
}
