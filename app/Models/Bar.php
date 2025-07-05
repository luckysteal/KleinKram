<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    public function drinks()
    {
        return $this->hasMany(Drink::class);
    }
}
