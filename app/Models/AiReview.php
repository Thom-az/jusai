<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReview extends Model
{
    use HasUuids;

    protected $fillable = [
        'organization_id',
        'legal_case_id',
        'document_id',
        'draft_id',
        'type',
        'prompt_used',
        'result',
        'status',
        'ai_model_used',
        'tokens_used',
        'confidence_score',
        'requires_human_review',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_human_review' => 'boolean',
            'reviewed_at' => 'datetime',
            'tokens_used' => 'integer',
            'confidence_score' => 'decimal:2',
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
