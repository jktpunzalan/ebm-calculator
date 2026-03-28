<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Individualization extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','library_id','patient_notes','baseline_risk_rc','relative_risk_rr','risk_on_treatment_rt','arr','nnt_nnh'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function library(): BelongsTo { return $this->belongsTo(StudyLibrary::class, 'library_id'); }
}
