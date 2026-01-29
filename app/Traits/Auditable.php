<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            // 1. Jika tiada sesiapa login (Guest Register), jangan rekod audit
            if (!Auth::check()) {
                return;
            }
            
            $model->recordAudit('created');
        });

        static::updated(function ($model) {
            $model->recordAudit('updated');
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted');
        });
    }

    protected function recordAudit($event)
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            $changes = $this->getDirty();
            foreach ($changes as $key => $value) {
                // Ignore timestamp columns to keep logs clean
                if (in_array($key, ['updated_at', 'created_at'])) continue;
                
                $oldValues[$key] = $this->getOriginal($key);
                $newValues[$key] = $value;
            }
            
            // If no actual relevant data changed, don't log
            if (empty($newValues)) return;

        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
            // Optional: Remove sensitive fields or timestamps from log
            unset($newValues['password'], $newValues['remember_token']);
            
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        // The AuditLog model's $casts property will handle the array-to-string conversion
        $this->audits()->create([
            'user_id'    => Auth::id() ?? '',
            'event'      => $event,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
        ]);
    }

    public function audits()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}