<?php

namespace App\Services\Siakad;

use App\Exceptions\Siakad\SiakadConfigException;
use App\Exceptions\Siakad\SiakadConnectionException;
use App\Exceptions\Siakad\SiakadResponseException;
use App\Support\Siakad\SiakadConfig;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SiakadApiService
{
    public function __construct(
        protected SiakadApiLogger $logger,
    ) {}

    /**
     * @param  array<string, scalar|null>  $query
     * @return array{records: list<array<string, mixed>>, total: int, correlation_id: string}
     */
    public function fetchAll(string $resourceKey, array $query = [], ?string $correlationId = null): array
    {
        $this->ensureConfigured();

        $correlationId ??= (string) Str::uuid();
        $endpoint = SiakadConfig::endpointPath($resourceKey);
        $limit = (int) config('siakad.pagination.limit', 500);
        $offset = 0;
        $allRecords = [];
        $total = 0;

        do {
            $pageQuery = array_merge($query, [
                'limit' => $limit,
                'offset' => $offset,
            ]);

            $page = $this->fetchPage($resourceKey, $endpoint, $pageQuery, $correlationId);
            $allRecords = array_merge($allRecords, $page['records']);
            $total = $page['total'];
            $offset += $limit;
        } while ($offset < $total);

        return [
            'records' => $allRecords,
            'total' => $total,
            'correlation_id' => $correlationId,
        ];
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array{records: list<array<string, mixed>>, total: int}
     */
    protected function fetchPage(
        string $resourceKey,
        string $endpoint,
        array $query,
        string $correlationId,
    ): array {
        $started = microtime(true);
        $purpose = 'reference_'.$resourceKey;

        try {
            $response = $this->httpClient()->get($endpoint, $query);
            $status = $response->status();
            $body = (string) $response->body();
            $durationMs = (int) round((microtime(true) - $started) * 1000);

            if ($response->failed()) {
                $message = $this->extractErrorMessage($response->json()) ?? 'Permintaan ke SIAKAD-API gagal.';
                $this->logger->log(
                    $purpose, 'GET', $endpoint, $query, null,
                    $status, $body, $durationMs, false, $message, $correlationId,
                );

                if ($status === 401 || $status === 403) {
                    throw new SiakadResponseException('Token SIAKAD-API tidak valid. Periksa konfigurasi server.');
                }

                throw new SiakadResponseException($message);
            }

            $json = $response->json();
            if (! is_array($json)) {
                $this->logger->log(
                    $purpose, 'GET', $endpoint, $query, null,
                    $status, $body, $durationMs, false, 'Response JSON tidak valid.', $correlationId,
                );
                throw new SiakadResponseException('Response SIAKAD-API tidak valid.');
            }

            if (($json['success'] ?? true) === false) {
                $message = (string) ($json['message'] ?? 'SIAKAD-API mengembalikan error.');
                $this->logger->log(
                    $purpose, 'GET', $endpoint, $query, null,
                    $status, $body, $durationMs, false, $message, $correlationId,
                );
                throw new SiakadResponseException($message);
            }

            $data = $json['data'] ?? [];
            if (! is_array($data)) {
                $data = [];
            }

            $meta = is_array($json['meta'] ?? null) ? $json['meta'] : [];
            $total = (int) ($meta['total'] ?? count($data));

            $this->logger->log(
                $purpose, 'GET', $endpoint, $query, null,
                $status, null, $durationMs, true, null, $correlationId,
            );

            return [
                'records' => array_values($data),
                'total' => $total,
            ];
        } catch (ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $this->logger->log(
                $purpose, 'GET', $endpoint, $query, null,
                null, null, $durationMs, false, $e->getMessage(), $correlationId,
            );
            throw new SiakadConnectionException(
                'Koneksi ke SIAKAD-API gagal. Periksa jaringan atau hubungi administrator.',
                0,
                $e,
            );
        } catch (RequestException $e) {
            $durationMs = (int) round((microtime(true) - $started) * 1000);
            $response = $e->response;
            $status = $response?->status();
            $body = $response ? (string) $response->body() : null;
            $message = $this->extractErrorMessage($response?->json()) ?? $e->getMessage();

            $this->logger->log(
                $purpose, 'GET', $endpoint, $query, null,
                $status, $body, $durationMs, false, $message, $correlationId,
            );

            throw new SiakadResponseException($message, 0, $e);
        }
    }

    protected function ensureConfigured(): void
    {
        if (! SiakadConfig::isConfigured()) {
            throw new SiakadConfigException(
                'Integrasi SIAKAD belum dikonfigurasi. Set SIAKAD_API_BASE_URL dan SIAKAD_API_TOKEN di .env.',
            );
        }
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

    /**
     * @param  mixed  $json
     */
    protected function extractErrorMessage(mixed $json): ?string
    {
        if (! is_array($json)) {
            return null;
        }

        $message = $json['message'] ?? null;

        return is_string($message) && $message !== '' ? $message : null;
    }
}
