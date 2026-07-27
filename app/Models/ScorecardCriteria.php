<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScorecardCriteria extends Model
{
    use HasFactory;

    protected $table = 'scorecard_criteria';

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function scorecard(): BelongsTo
    {
        return $this->belongsTo(InterviewScorecard::class, 'scorecard_id');
    }
}
