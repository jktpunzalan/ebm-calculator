<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug','title','authors','study_type','audience_tags','html_file_path','thumbnail_path','is_active','published_at'
    ];

    protected $casts = [
        'audience_tags' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function stats(): HasOne { return $this->hasOne(PubStatistic::class, 'publication_id'); }
    public function events(): HasMany { return $this->hasMany(PubEvent::class, 'publication_id'); }
}
