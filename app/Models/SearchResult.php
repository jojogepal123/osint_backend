<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchResult extends Model
{
    protected $fillable = [
        'search_query_id',
        'results',
    ];

    protected function casts(): array
    {
        return [
            'results' => 'array',
        ];
    }

    public function searchQuery(): BelongsTo
    {
        return $this->belongsTo(SearchQuery::class);
    }
}
