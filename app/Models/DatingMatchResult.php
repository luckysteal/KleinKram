<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUlids;

class DatingMatchResult extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'gender',
        'seeking',
        'franchise',
        'mapped_character',
        'traits',
        'partner_traits',
        'full_results',
    ];

    protected $casts = [
        'traits' => 'array',
        'partner_traits' => 'array',
        'full_results' => 'array',
    ];
}
