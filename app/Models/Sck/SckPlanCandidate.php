<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;

class SckPlanCandidate extends Model
{
    protected $guarded = [];
    protected $casts = ['feasible' => 'boolean', 'metrics' => 'array', 'tours' => 'array', 'unassigned' => 'array'];
    public function plan() { return $this->belongsTo(SckWeeklyPlan::class, 'weekly_plan_id'); }
}
