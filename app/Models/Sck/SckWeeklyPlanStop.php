<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckWeeklyPlanStop extends Model
{
    protected $guarded = [];
    protected $casts = ['allowed_weekdays' => 'array', 'required_date' => 'date', 'latitude' => 'float', 'longitude' => 'float'];
    public function plan() { return $this->belongsTo(SckWeeklyPlan::class, 'weekly_plan_id'); }
    public function customer() { return $this->belongsTo(SckCustomer::class, 'customer_id')->withTrashed(); }
}
