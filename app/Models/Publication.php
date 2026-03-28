<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'authors',
        'type',
        'audience_tags',
        'html_file_path',
        'thumbnail_path',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'audience_tags' => 'array',
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function stats(): HasOne
    {
        return $this->hasOne(PubStatistic::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PubEvent::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function recordEvent(string $type): void
    {
        $this->events()->create([
            'event_type' => $type,
            'ip_hash' => hash('sha256', request()->ip()),
            'user_agent' => request()->userAgent(),
        ]);

        $columnMap = [
            'view' => 'views',
            'share' => 'shares',
            'pdf_download' => 'pdf_downloads',
            'save' => 'saves',
        ];

        if (isset($columnMap[$type])) {
            DB::table('pub_statistics')
                ->where('publication_id', $this->id)
                ->increment($columnMap[$type]);
        }
    }
}
