<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'variant',
        'time',
        'event_name',
        'audio_file',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}