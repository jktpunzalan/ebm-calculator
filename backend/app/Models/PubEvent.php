<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PubEvent extends Model
{
    use HasFactory;

    public $timestamps = false; // using occurred_at only per spec

    protected $fillable = [
        'publication_id','event_type','user_agent','ip_hash','occurred_at'
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function publication(): BelongsTo { return $this->belongsTo(Publication::class); }
}
