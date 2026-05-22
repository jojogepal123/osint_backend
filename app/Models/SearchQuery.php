<?php

namespace App\Models;

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
    ];

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
}
