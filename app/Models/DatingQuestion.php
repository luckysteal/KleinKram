<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'type',
        'universe',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];
}
