<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drink extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'bar_id', 'icon_svg'];

    public function bar()
    {
        return $this->belongsTo(Bar::class);
    }
}
