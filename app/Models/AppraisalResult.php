<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'appraisal_id',
        'metric_key',
        'metric_value',
    ];

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }
}
