<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appraisal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','study_type','title','authors','year','checklist','notes','validity_score'
    ];

    protected $casts = [
        'checklist' => 'array',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function results(): HasMany { return $this->hasMany(AppraisalResult::class); }
}
