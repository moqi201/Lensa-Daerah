<?php

namespace App\Models\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait FetchesOpenData
{
    abstract protected static function cachePrefix(): string;
    abstract protected static function requestDataset(): array;

    public static function cacheKey(string $suffix): string
    {
        return static::cachePrefix() . ':' . $suffix;
    }

    public static function clearCache(): void
    {
        foreach (['fresh', 'last_good', 'cooldown'] as $suffix) {
            Cache::forget(static::cacheKey($suffix));
        }
    }

    public static function fetchFromApi(bool $forceRefresh = false): array
    {
        if (! $forceRefresh) {
            $cached = Cache::get(static::cacheKey('fresh'));
            if (static::isValidPayload($cached)) {
                return static::buildResult('CACHE', $cached);
            }
        }

        $attempt = static::requestDataset();

        if (($attempt['ok'] ?? false) === true && static::isValidPayload($attempt['payload'] ?? null)) {
            Cache::put(static::cacheKey('fresh'), $attempt['payload'], (int) config('puskesmas.cache.ttl', 3600));
            Cache::put(static::cacheKey('last_good'), $attempt['payload'], (int) config('puskesmas.cache.stale_ttl', 604800));

            return static::buildResult('LIVE_REST_API', $attempt['payload']);
        }

        $error = $attempt['error'] ?? 'Gagal mengambil data dari API.';
        $code  = $attempt['code'] ?? 'unknown_error';

        Log::warning('[Puskesmas] API gagal ditarik, beralih ke cadangan lokal', [
            'dataset' => static::cachePrefix(),
            'code'    => $code,
            'error'   => $error,
        ]);

        return static::fallbackResult($error, $code);
    }

    protected static function httpClient(): PendingRequest
    {
        $cfg = config('puskesmas.http');

        return Http::withOptions([
            'verify'           => false, // Bypass SSL langsung aktif tanpa syarat APP_ENV
            'force_ip_resolve' => 'v4',    // Force IPv4
            'timeout'          => (int) ($cfg['timeout'] ?? 10),
            'connect_timeout'  => (int) ($cfg['connect_timeout'] ?? 5),
        ])->withHeaders([
            'User-Agent' => $cfg['user_agent'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Accept'     => 'application/json',
        ]);
    }

    protected static function failure(string $code, string $message, array $context = []): array
    {
        return ['ok' => false, 'code' => $code, 'error' => $message, 'context' => $context];
    }

    protected static function fromException(\Throwable $e): array
    {
        return static::failure(
            'connection_error',
            'Koneksi ke API gagal atau melebihi batas waktu.',
            ['exception' => get_class($e), 'message' => $e->getMessage()]
        );
    }

    protected static function isValidPayload($payload): bool
    {
        return is_array($payload)
            && array_key_exists('records', $payload)
            && is_array($payload['records'])
            && ! empty($payload['records']);
    }

    protected static function fallbackResult(string $error, string $code): array
    {
        $stale = Cache::get(static::cacheKey('last_good'));

        if (static::isValidPayload($stale)) {
            return static::buildResult('CACHE_STALE', $stale, $error, $code);
        }

        // Ambil data cadangan lokal agar UI tidak pernah kosong/0
        $fallbackRecords = method_exists(static::class, 'getFallbackRecords') ? static::getFallbackRecords() : [];

        $fallbackPayload = [
            'records'    => $fallbackRecords,
            'fetched_at' => now()->toIso8601String(),
            'latency'    => 0,
            'quality'    => null,
            'regions'    => array_values(array_unique(array_map(fn($r) => mb_strtoupper($r['nama_kabupaten_kota'] ?? ''), $fallbackRecords))),
        ];

        return static::buildResult('LOCAL_FALLBACK_GUARD', $fallbackPayload, $error, $code);
    }

    protected static function buildResult(string $source, ?array $payload, ?string $error = null, ?string $code = null): array
    {
        $statusMap = [
            'LIVE_REST_API'        => 'success',
            'CACHE'                => 'cache',
            'CACHE_STALE'          => 'stale_cache',
            'LOCAL_FALLBACK_GUARD' => 'fallback',
        ];

        return [
            'status'     => $statusMap[$source] ?? 'fallback',
            'source'     => $source,
            'data'       => $payload['records'] ?? [],
            'latency'    => $payload['latency'] ?? null,
            'fetched_at' => $payload['fetched_at'] ?? null,
            'quality'    => $payload['quality'] ?? null,
            'payload'    => $payload,
            'error'      => $error,
            'error_code' => $code,
        ];
    }

    protected static function cleanString($value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}