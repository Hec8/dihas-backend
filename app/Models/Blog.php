<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{ 
    protected $fillable = [
        'titre',
        'contenu',
        'writer',
        'resume',
        'statut',
        'note',
        'slug',
        'image',
        'image_public_id', // Ajouté ici
    ];

    public function user():BelongsTo {
        return $this->belongsTo(User::class);
    }
}
