<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchlossgrabenJumpScore extends Model
{
    use HasFactory;

    protected $table = 'schlossgraben_jump_scores';

    protected $fillable = [
        'player_name',
        'score',
    ];
}
