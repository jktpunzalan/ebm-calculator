<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PubStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'views',
        'shares',
        'pdf_downloads',
        'saves',
    ];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->updated_at = now();
        });
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
