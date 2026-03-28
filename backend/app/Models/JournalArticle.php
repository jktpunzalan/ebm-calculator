<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','authors','abstract','doi','volume','issue','year','pdf_path','published_at'
    ];

    protected $casts = [
        'published_at' => 'date',
    ];
}
