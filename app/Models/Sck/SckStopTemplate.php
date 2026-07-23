<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SckStopTemplate extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['latitude' => 'float', 'longitude' => 'float', 'active' => 'boolean'];
    public function customer() { return $this->belongsTo(SckCustomer::class, 'customer_id')->withTrashed(); }
    public function items() { return $this->belongsToMany(SckWarehouseItem::class, 'sck_stop_template_items', 'stop_template_id', 'warehouse_item_id')->withPivot('suggested_quantity'); }
    public function tourStops() { return $this->hasMany(SckTourStop::class, 'stop_template_id'); }

    public function getFullAddressAttribute(): string
    {
        return trim(implode(', ', array_filter([trim(($this->street ?? '').' '.($this->house_number ?? '')), trim(($this->postal_code ?? '').' '.($this->city ?? '')), $this->country_code])));
    }
}
