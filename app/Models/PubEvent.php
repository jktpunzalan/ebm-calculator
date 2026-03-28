<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PubEvent extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'publication_id',
        'event_type',
        'ip_hash',
        'user_agent',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
