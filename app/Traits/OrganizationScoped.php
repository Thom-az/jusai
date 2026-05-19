<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;

trait OrganizationScoped
{
    protected function orgId(): string
    {
        return auth()->user()->organization_id;
    }

    protected function scopedQuery(string $model): Builder
    {
        return $model::where('organization_id', $this->orgId());
    }

    protected function logActivity(
        string $event,
        string $description,
        string $subjectType,
        string $subjectId,
        array $metadata = []
    ): void {
        ActivityLog::create([
            'organization_id' => $this->orgId(),
            'user_id'         => auth()->id(),
            'event'           => $event,
            'description'     => $description,
            'subject_type'    => $subjectType,
            'subject_id'      => $subjectId,
            'metadata'        => $metadata,
            'ip_address'      => request()->ip(),
            'created_at'      => now(),
        ]);
    }
}
