<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckCustomerChange extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['before' => 'array', 'after' => 'array', 'created_at' => 'datetime'];
    public function user() { return $this->belongsTo(\App\Models\User::class); }
}
