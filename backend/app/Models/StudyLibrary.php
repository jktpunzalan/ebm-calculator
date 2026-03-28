<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyLibrary extends Model
{
    use HasFactory;

    protected $table = 'study_library';

    protected $fillable = [
        'user_id','appraisal_id','title','study_type','validity_label','key_result_label','key_result_value','is_starred','saved_at'
    ];

    protected $casts = [
        'is_starred' => 'boolean',
        'saved_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function appraisal(): BelongsTo { return $this->belongsTo(Appraisal::class); }
}
