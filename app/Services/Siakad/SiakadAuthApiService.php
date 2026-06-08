<?php

namespace App\Services\Siakad;

use App\Exceptions\Siakad\SiakadConnectionException;
use App\Support\Siakad\SiakadConfig;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SiakadAuthApiService
{
    /**
     * @return array<string, mixed>|null
     */
    public function attemptLogin(string $login, string $password): ?array
    {
        $login = trim($login);
        if ($login === '' || $password === '') {
            return null;
        }

        $endpoint = (string) config('sipepeng_siakad_auth.login_endpoint', '/api/auth/login-app');

        try {
            $response = $this->httpClient()->post($endpoint, [
                'login' => $login,
                'password' => $password,
            ]);
            $response->throw();
        } catch (ConnectionException $e) {
            Log::error('Siakad auth connection failed.', ['message' => $e->getMessage()]);
            throw new SiakadConnectionException(
                'Koneksi ke Siakad-API gagal. Periksa jaringan atau konfigurasi URL di pengaturan aplikasi.',
            );
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            if (in_array($status, [401, 403, 422], true)) {
                return null;
            }

            Log::warning('Siakad auth request failed.', [
                'status' => $status,
                'message' => $exception->getMessage(),
            ]);

            throw new SiakadConnectionException(
                'Layanan Siakad-API tidak dapat dihubungi saat ini. Coba beberapa saat lagi.',
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            return null;
        }

        $data = $json['data'] ?? $json['user'] ?? $json;
        if (! is_array($data)) {
            return null;
        }

        if (($json['success'] ?? true) === false) {
            return null;
        }

        return $data;
    }

    protected function httpClient()
    {
        $client = Http::timeout(SiakadConfig::timeout())
            ->acceptJson()
            ->baseUrl(SiakadConfig::baseUrl());

        if (SiakadConfig::token() !== '') {
            $client = $client->withToken(SiakadConfig::token());
        }

        $apiHost = trim((string) config('siakad.api_host', ''));
        if ($apiHost !== '' && str_contains(SiakadConfig::baseUrl(), '127.0.0.1')) {
            $client = $client->withHeaders(['Host' => $apiHost]);
        }

        return $client;
    }
}
