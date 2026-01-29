<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the trait.
     * Laravel automatically calls boot{TraitName} on model boot.
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->recordAudit('created');
        });

        static::updated(function ($model) {
            $model->recordAudit('updated');
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted');
        });
    }

    /**
     * Record the audit entry.
     *
     * @param string $event
     * @return void
     */
    protected function recordAudit($event)
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            // Get changed attributes
            $changes = $this->getDirty();
            
            // Get original values for those changed attributes
            foreach ($changes as $key => $value) {
                $oldValues[$key] = $this->getOriginal($key);
                $newValues[$key] = $value;
            }
        } elseif ($event === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($event === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        // Create the audit record relationship
        $this->audits()->create([
            'user_id'    => Auth::id(), // Records who did the action
            'event'      => $event,
            'old_values' => !empty($oldValues) ? json_encode($oldValues) : null,
            'new_values' => !empty($newValues) ? json_encode($newValues) : null,
           // 'ip_address' => request()->ip(),
            //'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Polymorphic relationship to audits.
     */
    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable');
    }
}