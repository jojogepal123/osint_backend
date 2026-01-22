<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $fillable = [
        'query',
        'user_id',
        'ip_address',
    ];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
