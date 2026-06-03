<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    use HasUuids;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
    ];

    protected function casts(): array
    {
        return [
            'tokens_used' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class);
    }
}
