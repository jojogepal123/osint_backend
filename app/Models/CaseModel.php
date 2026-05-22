<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseModel extends Model
{
    use HasFactory;

    protected $table = 'cases';

    protected $fillable = [
        'user_id',
        'case_number',
        'title',
        'description',
        'status',
        'priority',
        'category',
        'assigned_to',
        'team_id',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'case_assignments', 'case_id', 'user_id')
            ->withTimestamps();
    }

    public function assignedUsersArray(): HasMany
    {
        return $this->hasMany(CaseAssignment::class, 'case_id');
    }

    public function searchQueries(): HasMany
    {
        return $this->hasMany(SearchQuery::class, 'case_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CaseActivity::class, 'case_id');
    }
}
