<?php

namespace App\Services\Siakad;

use App\Models\SiakadReferenceCache;
use App\Services\AppSettingsService;
use Illuminate\Support\Carbon;

class SiakadReferenceCacheService
{
    public function __construct(
        protected AppSettingsService $settings,
    ) {}

    public function isEnabled(): bool
    {
        return $this->settings->getBool('siakad', 'cache_enabled', true);
    }

    public function ttlMinutes(): int
    {
        return max(1, $this->settings->getInt('siakad', 'cache_ttl_minutes', 360));
    }

    public function getFresh(string $resourceKey): ?SiakadReferenceCache
    {
        $cache = $this->find($resourceKey);
        if ($cache === null) {
            return null;
        }

        if ($cache->expires_at !== null && $cache->expires_at->isPast()) {
            return null;
        }

        return $cache;
    }

    public function getStale(string $resourceKey): ?SiakadReferenceCache
    {
        return $this->find($resourceKey);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @param  array<string, mixed>  $meta
     */
    public function put(
        string $resourceKey,
        array $records,
        array $meta,
        string $correlationId,
    ): SiakadReferenceCache {
        $ttl = $this->ttlMinutes();
        $fetchedAt = now();

        return SiakadReferenceCache::query()->updateOrCreate(
            ['resource_key' => $resourceKey],
            [
                'payload' => $records,
                'record_count' => count($records),
                'meta' => $meta,
                'fetched_at' => $fetchedAt,
                'expires_at' => $fetchedAt->copy()->addMinutes($ttl),
                'correlation_id' => $correlationId,
            ],
        );
    }

    public function forget(string $resourceKey): void
    {
        SiakadReferenceCache::query()->where('resource_key', $resourceKey)->delete();
    }

    public function forgetAll(): void
    {
        SiakadReferenceCache::query()->delete();
    }

    protected function find(string $resourceKey): ?SiakadReferenceCache
    {
        return SiakadReferenceCache::query()
            ->where('resource_key', $resourceKey)
            ->first();
    }
}
