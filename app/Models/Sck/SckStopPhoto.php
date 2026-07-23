<?php

namespace App\Models\Sck;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SckStopPhoto extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public function stop() { return $this->belongsTo(SckTourStop::class, 'tour_stop_id'); }
    public function customer() { return $this->belongsTo(SckCustomer::class, 'customer_id')->withTrashed(); }
    public function comments() { return $this->morphMany(SckComment::class, 'commentable')->latest(); }
}
