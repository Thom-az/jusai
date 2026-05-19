<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'plan',
        'status',
        'billing_cycle',
        'price_cents',
        'currency',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'payment_gateway',
        'gateway_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'canceled_at' => 'datetime',
            'price_cents' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getMrrAttribute(): float
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return $this->billing_cycle === 'annual'
            ? $this->price_cents / 12 / 100
            : $this->price_cents / 100;
    }
}
