<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'app_mode',
        'credits',
        'is_admin',
        'cms_role',
        'team_id',
        'active_case_id',
        'otp',
        'otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime',
            'last_otp_verified_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    public function ips()
    {
        return $this->hasMany(UserIp::class);
    }

    public function searchQueries()
    {
        return $this->hasMany(\App\Models\SearchQuery::class);
    }

    public function apiEngines()
    {
        return $this->belongsToMany(ApiEngine::class, 'user_api_permissions');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function activeCase(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'active_case_id');
    }

    public function assignedCases(): HasMany
    {
        return $this->hasMany(CaseModel::class, 'assigned_to');
    }

    public function createdCases(): HasMany
    {
        return $this->hasMany(CaseModel::class, 'user_id');
    }

    public function getCmsRoleAttribute($value): string
    {
        if ($this->is_admin) {
            return 'supervisor';
        }

        return $value ?? 'auditor';
    }
}
