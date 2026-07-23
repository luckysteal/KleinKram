<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckTourStopItem extends Model
{
    protected $guarded = [];
    protected $casts = ['quantity' => 'decimal:2', 'ek_snapshot' => 'decimal:2', 'vk_snapshot' => 'decimal:2', 'actual_net_price' => 'decimal:2', 'tax_rate' => 'decimal:2', 'stock_deducted' => 'decimal:2'];
    public function warehouseItem() { return $this->belongsTo(SckWarehouseItem::class, 'warehouse_item_id'); }
}
