<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','role','bio','photo_path','credentials','contact','display_order','is_active'
    ];

    protected $casts = [
        'credentials' => 'array',
        'contact' => 'array',
        'is_active' => 'boolean',
    ];
}
