<?php

namespace App\Services\Siakad;

use App\Exceptions\Siakad\SiakadApiException;
use App\Services\AppSettingsService;
use App\Support\Siakad\SiakadResource;
use Illuminate\Support\Str;

class SiakadReferenceService
{
    public function __construct(
        protected SiakadApiService $api,
        protected SiakadReferenceCacheService $cache,
        protected SiakadReferenceFilter $filter,
        protected AppSettingsService $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function forTab(string $tab, array $filters = [], bool $forceRefresh = false): array
    {
        $tab = $this->normalizeTab($tab);
        $resourceKey = $this->resourceKeyForTab($tab);

        try {
            $loaded = $this->loadRecords($resourceKey, $forceRefresh);
            $records = $loaded['records'];

            if ($tab === 'semester') {
                $records = $this->mapSemesterRecords($records);
            }

            $filtered = $this->filter->apply($tab, $records, $filters);

            return [
                'tab' => $tab,
                'records' => $filtered,
                'total' => count($records),
                'filtered_total' => count($filtered),
                'meta' => [
                    'source' => $loaded['source'],
                    'fetched_at' => $loaded['fetched_at'],
                    'is_stale' => $loaded['is_stale'],
                    'cache_enabled' => $this->cache->isEnabled(),
                    'ttl_minutes' => $this->cache->ttlMinutes(),
                ],
                'error' => null,
                'filters' => $filters,
                'prodi_options' => $this->prodiOptions(),
            ];
        } catch (SiakadApiException $e) {
            $stale = $this->tryStaleFallback($resourceKey, $tab, $filters);

            if ($stale !== null) {
                $stale['error'] = $e->getMessage();
                $stale['meta']['is_stale'] = true;
                $stale['meta']['source'] = 'stale_cache';

                return $stale;
            }

            return [
                'tab' => $tab,
                'records' => [],
                'total' => 0,
                'filtered_total' => 0,
                'meta' => [
                    'source' => 'none',
                    'fetched_at' => null,
                    'is_stale' => false,
                    'cache_enabled' => $this->cache->isEnabled(),
                    'ttl_minutes' => $this->cache->ttlMinutes(),
                ],
                'error' => $e->getMessage(),
                'filters' => $filters,
                'prodi_options' => $this->prodiOptionsFromCache(),
            ];
        }
    }

    public function refresh(string $tab): array
    {
        $tab = $this->normalizeTab($tab);

        if ($tab === 'all') {
            foreach (SiakadResource::all() as $resourceKey) {
                $this->cache->forget($resourceKey);
            }
            $tab = 'prodi';
        } else {
            $this->cache->forget($this->resourceKeyForTab($tab));
        }

        return $this->forTab($tab, [], true);
    }

    /**
     * @return array{records: list<array<string, mixed>>, source: string, fetched_at: ?\Illuminate\Support\Carbon, is_stale: bool}
     */
    protected function loadRecords(string $resourceKey, bool $forceRefresh): array
    {
        if (! $forceRefresh && $this->cache->isEnabled()) {
            $fresh = $this->cache->getFresh($resourceKey);
            if ($fresh !== null) {
                return [
                    'records' => $fresh->payload ?? [],
                    'source' => 'cache',
                    'fetched_at' => $fresh->fetched_at,
                    'is_stale' => false,
                ];
            }
        }

        $correlationId = (string) Str::uuid();
        $apiQuery = $this->apiQueryForResource($resourceKey);
        $result = $this->api->fetchAll($resourceKey, $apiQuery, $correlationId);

        if ($this->cache->isEnabled()) {
            $cached = $this->cache->put(
                $resourceKey,
                $result['records'],
                ['api_total' => $result['total'], 'query' => $apiQuery],
                $result['correlation_id'],
            );

            return [
                'records' => $result['records'],
                'source' => 'api',
                'fetched_at' => $cached->fetched_at,
                'is_stale' => false,
            ];
        }

        return [
            'records' => $result['records'],
            'source' => 'api',
            'fetched_at' => now(),
            'is_stale' => false,
        ];
    }

    /**
     * @return array<string, scalar|null>
     */
    protected function apiQueryForResource(string $resourceKey): array
    {
        return match ($resourceKey) {
            SiakadResource::STATUS_MAHASISWA => ['tipe' => 'operasional'],
            default => [],
        };
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return list<array<string, mixed>>
     */
    protected function mapSemesterRecords(array $records): array
    {
        return array_values(array_map(fn (array $row): array => [
            'siakad_id' => $row['siakad_id'] ?? '',
            'nama_tahun_akademik' => $row['nama_tahun_akademik'] ?? '',
            'semester' => $row['semester'] ?? null,
            'is_active' => $row['is_active'] ?? false,
        ], $records));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|null
     */
    protected function tryStaleFallback(string $resourceKey, string $tab, array $filters): ?array
    {
        $staleCache = $this->cache->getStale($resourceKey);
        if ($staleCache === null) {
            return null;
        }

        $records = $staleCache->payload ?? [];
        if ($tab === 'semester') {
            $records = $this->mapSemesterRecords($records);
        }

        $filtered = $this->filter->apply($tab, $records, $filters);

        return [
            'tab' => $tab,
            'records' => $filtered,
            'total' => count($records),
            'filtered_total' => count($filtered),
            'meta' => [
                'source' => 'stale_cache',
                'fetched_at' => $staleCache->fetched_at,
                'is_stale' => true,
                'cache_enabled' => $this->cache->isEnabled(),
                'ttl_minutes' => $this->cache->ttlMinutes(),
            ],
            'error' => null,
            'filters' => $filters,
            'prodi_options' => $this->prodiOptionsFromCache(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function prodiOptions(): array
    {
        try {
            $loaded = $this->loadRecords(SiakadResource::PRODI, false);
        } catch (SiakadApiException) {
            return $this->prodiOptionsFromCache();
        }

        return $this->buildProdiOptions($loaded['records']);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function prodiOptionsFromCache(): array
    {
        $cache = $this->cache->getStale(SiakadResource::PRODI);

        return $this->buildProdiOptions($cache?->payload ?? []);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return list<array{value: string, label: string}>
     */
    protected function buildProdiOptions(array $records): array
    {
        $options = [];
        foreach ($records as $row) {
            $id = (string) ($row['siakad_id'] ?? $row['kode_prodi'] ?? '');
            if ($id === '') {
                continue;
            }
            $options[] = [
                'value' => $id,
                'label' => trim((string) ($row['nama_prodi'] ?? $id)),
            ];
        }

        usort($options, fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return $options;
    }

    protected function normalizeTab(string $tab): string
    {
        $allowed = ['prodi', 'dosen', 'mahasiswa', 'tahun_akademik', 'semester', 'all'];

        return in_array($tab, $allowed, true) ? $tab : 'prodi';
    }

    protected function resourceKeyForTab(string $tab): string
    {
        return match ($tab) {
            'prodi' => SiakadResource::PRODI,
            'dosen' => SiakadResource::DOSEN,
            'mahasiswa' => SiakadResource::MAHASISWA,
            'tahun_akademik', 'semester' => SiakadResource::TAHUN_AKADEMIK,
            default => SiakadResource::PRODI,
        };
    }
}
