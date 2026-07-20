<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SckWarehouseLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'item_id', 'success', 'action', 'type', 'message'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Sck\SckWarehouseItem::class, 'item_id');
    }
}
