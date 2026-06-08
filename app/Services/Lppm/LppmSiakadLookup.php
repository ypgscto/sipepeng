<?php

namespace App\Services\Lppm;

use App\Services\Siakad\SiakadReferenceService;

class LppmSiakadLookup
{
    public function __construct(
        protected SiakadReferenceService $reference,
    ) {}

    /**
     * @return list<array{value: string, label: string}>
     */
    public function prodiOptions(): array
    {
        $data = $this->reference->forTab('prodi');

        return $data['prodi_options'] ?? [];
    }

    /**
     * @return list<array{value: string, label: string, nama?: string, prodi_id?: string, prodi_nama?: string}>
     */
    public function dosenOptions(): array
    {
        $data = $this->reference->forTab('dosen');
        $options = [];
        foreach ($data['records'] as $row) {
            $id = (string) ($row['siakad_id'] ?? '');
            if ($id === '') {
                continue;
            }
            $nama = (string) ($row['nama_dosen'] ?? $row['nama'] ?? $id);
            $options[] = [
                'value' => $id,
                'label' => $nama,
                'nama' => $nama,
                'prodi_id' => (string) ($row['prodi_id'] ?? $row['kode_prodi'] ?? ''),
                'prodi_nama' => (string) ($row['nama_prodi'] ?? ''),
            ];
        }

        return $options;
    }

    public function prodiName(string $id): string
    {
        foreach ($this->prodiOptions() as $opt) {
            if ($opt['value'] === $id) {
                return $opt['label'];
            }
        }

        return $id;
    }
}
