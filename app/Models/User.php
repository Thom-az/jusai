<?php

namespace App\Models;

use App\Traits\HasOrgPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasOrgPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'role',
        'is_active',
        'phone',
        'oab_number',
        'oab_uf',
        'avatar',
        'job_title',
        'theme',
        'timezone',
        'notification_prefs',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'two_factor_confirmed_at'    => 'datetime',
            'password'                   => 'hashed',
            'is_active'                  => 'boolean',
            'notification_prefs'         => 'array',
            'two_factor_secret'          => 'encrypted',
            'two_factor_recovery_codes'  => 'encrypted:array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function legalCases(): HasMany
    {
        return $this->hasMany(LegalCase::class, 'created_by');
    }

    public function assignedCases(): HasMany
    {
        return $this->hasMany(LegalCase::class, 'assigned_to');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'opened_by');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function belongsToOrganization(): bool
    {
        return $this->organization_id !== null;
    }
}
