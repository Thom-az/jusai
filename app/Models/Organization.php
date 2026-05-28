<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasUuids, Auditable;

    protected string $auditLabel = 'Escritório';

    protected $fillable = [
        'name',
        'legal_name',
        'slug',
        'email',
        'phone',
        'document',
        'status',
        'plan',
        'trial_ends_at',
        'zip_code',
        'street',
        'street_number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'logo',
        'logo_dark',
        'practice_areas',
        'password_policy',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at'   => 'datetime',
            'practice_areas'  => 'array',
            'password_policy' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function legalCases(): HasMany
    {
        return $this->hasMany(LegalCase::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }

    public function aiReviews(): HasMany
    {
        return $this->hasMany(AiReview::class);
    }

    public function subscription(): HasOne
    {
        // latestOfMany generates MAX(uuid) which fails in PostgreSQL; latest() uses ORDER BY + LIMIT 1.
        return $this->hasOne(Subscription::class)->latest('created_at');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
