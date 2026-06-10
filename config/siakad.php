<?php

use App\Support\Siakad\SiakadResource;

return [

    'base_url' => rtrim((string) env('SIAKAD_API_BASE_URL', ''), '/'),

    'token' => (string) env('SIAKAD_API_TOKEN', ''),

    'timeout' => (int) env('SIAKAD_API_TIMEOUT', 120),

    'api_host' => env('SIAKAD_API_HOST'),

    'pagination' => [
        'limit' => (int) env('SIAKAD_API_PAGE_LIMIT', 500),
    ],

    /*
    | Path endpoint relatif terhadap base_url. Override via .env tanpa ubah kode.
    */
    'endpoints' => [
        SiakadResource::PRODI => env('SIAKAD_ENDPOINT_PRODI', '/api/simawa/prodi'),
        SiakadResource::TAHUN_AKADEMIK => env('SIAKAD_ENDPOINT_TAHUN_AKADEMIK', '/api/simawa/tahun-akademik'),
        SiakadResource::DOSEN => env('SIAKAD_ENDPOINT_DOSEN', '/api/simawa/dosen'),
        SiakadResource::MAHASISWA => env('SIAKAD_ENDPOINT_MAHASISWA', '/api/simawa/mahasiswa'),
        SiakadResource::STATUS_MAHASISWA => env('SIAKAD_ENDPOINT_STATUS_MAHASISWA', '/api/simawa/status-mahasiswa'),
        SiakadResource::LOGIN_USERS => env('SIAKAD_ENDPOINT_LOGIN_USERS', '/api/sipepeng/login-users'),
    ],

    'sync_max_execution_seconds' => (int) env('SIAKAD_SYNC_MAX_EXECUTION_SECONDS', 900),

];
