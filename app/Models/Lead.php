<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'company_size',
        'area_of_interest',
        'source',
        'status',
        'lost_reason',
        'estimated_value_cents',
        'assigned_to',
        'notes',
        'converted_at',
        'converted_organization_id',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
            'estimated_value_cents' => 'integer',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'converted_organization_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(LeadInteraction::class);
    }
}
