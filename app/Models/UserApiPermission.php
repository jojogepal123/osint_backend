<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserApiPermission extends Model
{
    protected $table = 'user_api_permissions';

    public $timestamps = false;

    protected $fillable = ['user_id', 'api_engine_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiEngine(): BelongsTo
    {
        return $this->belongsTo(ApiEngine::class, 'api_engine_id');
    }
}
