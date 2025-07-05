<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'global_tax_enabled',
        'german_tax_enabled',
        'church_tax_enabled',
        'badges',
    ];

    protected $casts = [
        'badges' => 'array',
    ];
}
