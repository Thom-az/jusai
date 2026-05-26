<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasUuids, Auditable;

    protected string $auditLabel = 'Documento';

    protected array $auditExclude = ['storage_path', 'ai_extracted_at'];

    protected $fillable = [
        'organization_id',
        'legal_case_id',
        'title',
        'original_filename',
        'storage_path',
        'file_size',
        'mime_type',
        'status',
        'ai_summary',
        'ai_extracted_at',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'ai_extracted_at' => 'datetime',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function aiReviews(): HasMany
    {
        return $this->hasMany(AiReview::class);
    }
}
