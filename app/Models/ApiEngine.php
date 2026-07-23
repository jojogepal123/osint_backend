<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ApiEngine extends Model
{
    protected $table = 'api_engines';

    protected $fillable = ['name', 'slug', 'category'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_api_permissions')
            ->withTimestamps();
    }
}
