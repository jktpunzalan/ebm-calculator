<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PubStatistic extends Model
{
    use HasFactory;

    public $timestamps = false; // maintains only updated_at per spec

    protected $fillable = [
        'publication_id','views','shares','pdf_downloads','saves','updated_at'
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function publication(): BelongsTo { return $this->belongsTo(Publication::class); }
}
