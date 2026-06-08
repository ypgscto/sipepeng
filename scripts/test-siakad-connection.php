<?php

/**
 * Uji koneksi SiPepeng → Siakad-API (tanpa masalah quoting PowerShell).
 *
 * Jalankan di server:
 *   cd C:\webserver\www\sipepeng
 *   php scripts\test-siakad-connection.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);

if (! is_file($root.'/vendor/autoload.php')) {
    fwrite(STDERR, "Jalankan dari folder proyek setelah: composer install\n");
    exit(1);
}

require $root.'/vendor/autoload.php';

$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Siakad\SiakadApiService;
use App\Support\Siakad\SiakadConfig;
use App\Support\Siakad\SiakadResource;

$base = SiakadConfig::baseUrl();
$token = SiakadConfig::token();

echo 'SIAKAD_API_BASE_URL: '.($base !== '' ? $base : '(kosong)').PHP_EOL;
echo 'SIAKAD_API_TOKEN: '.($token !== '' ? '(terisi, '.strlen($token).' karakter)' : '(kosong)').PHP_EOL;

if ($base === '') {
    echo PHP_EOL.'ERROR: Isi SIAKAD_API_BASE_URL di .env atau Pengaturan → SIAKAD-API, lalu: php artisan config:clear'.PHP_EOL;
    exit(1);
}

$endpoint = SiakadConfig::endpointPath(SiakadResource::PRODI);
echo "Mencoba GET: {$base}{$endpoint}?limit=1".PHP_EOL.PHP_EOL;

try {
    $page = app(SiakadApiService::class)->fetchAll(SiakadResource::PRODI);
    $rows = count($page['records'] ?? []);

    echo "OK - Siakad terhubung (contoh baris prodi: {$rows}, total: ".($page['total'] ?? 0).').'.PHP_EOL;
    exit(0);
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage().PHP_EOL;

    if (str_contains($e->getMessage(), 'could not be found') || str_contains($e->getMessage(), '404')) {
        echo PHP_EOL.'Kemungkinan: Siakad-API di server belum versi terbaru (tanpa route /api/simawa/*).'.PHP_EOL;
        echo 'Perbarui proyek siakad-api di C:\\webserver\\www\\siakad-api dari development.'.PHP_EOL;
        echo 'Cek di browser/Postman: '.$base.'/api/health'.PHP_EOL;
        echo 'Dan: '.$base.$endpoint.'?limit=1 (dengan Bearer token).'.PHP_EOL;
    }

    exit(1);
}
