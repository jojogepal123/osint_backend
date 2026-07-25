<?php

namespace App\Models;

use App\Services\SearchCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SearchQuery extends Model
{
    protected $fillable = [
        'query',
        'type',
        'user_id',
        'ip_address',
        'case_id',
        'public_id',
        'query_hash',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $query) {
            if (empty($query->public_id)) {
                $query->public_id = SearchCache::generatePublicId();
            }

            if (empty($query->query_hash) && ! empty($query->type)) {
                $query->query_hash = SearchCache::hash($query->type, $query->query);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CaseModel::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(\App\Models\SearchResult::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
