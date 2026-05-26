<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Draft extends Model
{
    use HasUuids, Auditable;

    protected string $auditLabel = 'Minuta';

    protected array $auditExclude = ['ai_model_used'];

    protected $fillable = [
        'organization_id',
        'legal_case_id',
        'title',
        'type',
        'content',
        'status',
        'version',
        'generated_by_ai',
        'ai_model_used',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'generated_by_ai' => 'boolean',
            'reviewed_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function legalCase(): BelongsTo
    {
        return $this->belongsTo(LegalCase::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aiReviews(): HasMany
    {
        return $this->hasMany(AiReview::class);
    }
}
