<?php

namespace App\DataTransferObjects\Report;

class ReportFilterDto
{
    public function __construct(
        public ?string $tahunAkademikId = null,
        public ?string $tahunAkademikNama = null,
        public ?string $semesterId = null,
        public ?string $semesterNama = null,
        public ?string $prodiId = null,
        public ?string $dosenId = null,
        public ?string $status = null,
        public ?int $calendarYear = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tahun_akademik_id' => $this->tahunAkademikId,
            'semester_id' => $this->semesterId,
            'prodi_id' => $this->prodiId,
            'dosen_id' => $this->dosenId,
            'status' => $this->status,
            'calendar_year' => $this->calendarYear,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];
    }
}
