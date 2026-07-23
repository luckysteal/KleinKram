<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SckComment extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['edited_at' => 'datetime'];
    public function commentable() { return $this->morphTo(); }
    public function user() { return $this->belongsTo(\App\Models\User::class); }
}
