<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadInteraction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'notes',
        'outcome',
        'scheduled_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
