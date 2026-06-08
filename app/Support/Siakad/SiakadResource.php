<?php

namespace App\Support\Siakad;

final class SiakadResource
{
    public const PRODI = 'prodi';

    public const TAHUN_AKADEMIK = 'tahun_akademik';

    public const DOSEN = 'dosen';

    public const MAHASISWA = 'mahasiswa';

    public const STATUS_MAHASISWA = 'status_mahasiswa';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PRODI,
            self::TAHUN_AKADEMIK,
            self::DOSEN,
            self::MAHASISWA,
            self::STATUS_MAHASISWA,
        ];
    }
}
