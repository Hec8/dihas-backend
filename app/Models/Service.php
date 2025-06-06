<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'is_active',
        'icon',
        'public_icon_id'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
