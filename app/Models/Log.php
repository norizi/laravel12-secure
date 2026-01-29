<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'extra',
    ];

    // Cast JSON fields ke array
    protected $casts = [
        'context' => 'array',
        'extra'   => 'array',
    ];
}
