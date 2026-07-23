<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SckWarehouseLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'item_id', 'tour_id', 'tour_stop_id', 'quantity', 'invoice_hash', 'success', 'action', 'type', 'message'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Sck\SckWarehouseItem::class, 'item_id');
    }
}
