<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model) {
            $model->recordAudit('created', [], $model->auditAttributes());
        });

        static::updating(function (self $model) {
            $dirty = array_diff_key($model->getDirty(), array_flip($model->auditExcludedFields()));

            if (! empty($dirty)) {
                $model->_auditSnapshot = [
                    'old' => array_intersect_key($model->getOriginal(), $dirty),
                    'new' => $dirty,
                ];
            }
        });

        static::updated(function (self $model) {
            if (empty($model->_auditSnapshot)) {
                return;
            }

            $model->recordAudit('updated', $model->_auditSnapshot['old'], $model->_auditSnapshot['new']);
            unset($model->_auditSnapshot);
        });

        static::deleted(function (self $model) {
            $model->recordAudit('deleted', $model->auditAttributes(), []);
        });
    }

    protected function recordAudit(string $action, array $old, array $new): void
    {
        try {
            ActivityLog::create([
                'organization_id' => $this->organization_id ?? null,
                'user_id'         => Auth::id(),
                'event'           => $action,
                'description'     => $this->auditDescription($action),
                'subject_type'    => static::class,
                'subject_id'      => (string) $this->getKey(),
                'metadata'        => array_filter(['old' => $old, 'new' => $new]),
                'ip_address'      => request()->ip(),
                'created_at'      => now(),
            ]);
        } catch (\Throwable) {
            // Audit failure must never interrupt the main operation.
        }
    }

    protected function auditDescription(string $action): string
    {
        $label = property_exists($this, 'auditLabel')
            ? $this->auditLabel
            : class_basename(static::class);

        return match ($action) {
            'created' => "{$label} criado",
            'updated' => "{$label} atualizado",
            'deleted' => "{$label} excluído",
            default   => "{$label} {$action}",
        };
    }

    protected function auditAttributes(): array
    {
        return array_diff_key($this->getAttributes(), array_flip($this->auditExcludedFields()));
    }

    protected function auditExcludedFields(): array
    {
        $defaults = ['created_at', 'updated_at', 'deleted_at', 'description', 'internal_notes', 'content', 'ai_summary'];

        return array_merge($defaults, $this->auditExclude ?? []);
    }

    public function activityLogs()
    {
        return ActivityLog::where('subject_type', static::class)
            ->where('subject_id', (string) $this->getKey())
            ->latest('created_at');
    }
}
