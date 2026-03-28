<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyLibrary extends Model
{
    use HasFactory;

    protected $table = 'study_library';

    protected $fillable = [
        'user_id',
        'appraisal_id',
        'title',
        'study_type',
        'validity_label',
        'key_result_label',
        'key_result_value',
        'is_starred',
    ];

    protected $casts = [
        'is_starred' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function individualizations(): HasMany
    {
        return $this->hasMany(Individualization::class, 'library_id');
    }
}
