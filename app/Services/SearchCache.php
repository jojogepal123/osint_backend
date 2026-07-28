<?php

namespace App\Services;

use App\Models\SearchQuery;
use Illuminate\Support\Str;

class SearchCache
{
    /**
     * Find an existing SearchQuery that has already been searched with the same value
     * (after normalization) and the same type. Returns the SearchQuery with its
     * SearchResult eager-loaded, or null if no cache hit.
     */
    public static function findHit(string $type, mixed $value): ?SearchQuery
    {
        $hash = self::hash($type, $value);

        if ($hash === null) {
            return null;
        }

        return SearchQuery::with('result')
            ->where('type', $type)
            ->where('query_hash', $hash)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Build a stable cache key. Returns null for empty / unhashable inputs.
     *
     * Normalization:
     *  - email: trim + lowercase, strip whitespace
     *  - phone: strip everything except digits, then prefix with '+'
     *  - everything else: trim
     *
     * For corporate/verification, callers pass an array which we JSON-encode
     * with sorted keys so {"a":1,"b":2} and {"b":2,"a":1} hash to the same value.
     */
    public static function hash(string $type, mixed $value): ?string
    {
        $normalized = self::normalize($type, $value);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        return hash('sha256', $type.'|'.$normalized);
    }

    public static function normalize(string $type, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = self::canonicalJson($value);
        } else {
            $value = (string) $value;
        }

        return match ($type) {
            'email' => strtolower(preg_replace('/\s+/', '', trim($value))),
            'phone' => self::normalizePhone($value),
            'vehicle' => strtoupper(preg_replace('/\s+/', '', trim($value))),
            'challan' => strtoupper(preg_replace('/\s+/', '', trim($value))),
            'upi' => strtolower(trim($value)),
            default => trim($value),
        };
    }

    private static function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        if ($digits === null || $digits === '') {
            return '';
        }

        return '+'.$digits;
    }

    private static function canonicalJson(array $data): string
    {
        $sorted = [];
        foreach ($data as $k => $v) {
            $sorted[$k] = is_array($v) ? self::canonicalJson($v) : $v;
        }
        ksort($sorted);

        return json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Return the saved cached body as a fresh shallow copy, without mutating
     * the stored row in the database. Returns null when the saved result body
     * is empty or missing.
     *
     * Callers may add or overwrite top-level keys on the returned array (e.g.
     * `credits`) without affecting the persisted cached body.
     */
    public static function cachedResponsePayload(SearchQuery $cached, float $currentCredits): ?array
    {
        $result = $cached->result;

        if (! $result) {
            return null;
        }

        $body = $result->results;

        if (! is_array($body) || empty($body)) {
            return null;
        }

        return $body;
    }

    public static function generatePublicId(): string
    {
        return (string) Str::uuid();
    }
}
