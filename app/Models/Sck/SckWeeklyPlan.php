<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SckWeeklyPlan extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['week_start' => 'date', 'parameters' => 'array'];
    public function stops() { return $this->hasMany(SckWeeklyPlanStop::class, 'weekly_plan_id'); }
    public function candidates() { return $this->hasMany(SckPlanCandidate::class, 'weekly_plan_id'); }
    public function selectedCandidate() { return $this->belongsTo(SckPlanCandidate::class, 'selected_candidate_id'); }
    public function tours() { return $this->hasMany(SckTour::class, 'weekly_plan_id'); }
}
