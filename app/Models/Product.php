<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'homepage_image',
        'short_description',
        'logo',
        'long_description',
        'location',
        'type',
        'industry',
        'monetization',
        'estimated_profit',
        'estimated_revenue',
        'detail_images',
        'why_buy',
        'main_features',
        'admin_features',
        'economic_model',
        'data_security',
        'last_updated',
        'is_published',
    ];

    //Indiquer à laravel que 'detail_images' doit être traité comme un tableau
    protected $casts = [
        'detail_images' => 'array',
        'is_published' => 'boolean',
        'last_updated' => 'date',//optionnel pour le traiter comme objet Date 
    ];
}
