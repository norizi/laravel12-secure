<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
    ];

    /**
     * The attributes that should be cast.
     *
     * This ensures that the arrays sent from the Trait are 
     * automatically converted to JSON strings for the database.
     */
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}