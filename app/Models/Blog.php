<?php

namespace App\Models;

use App\Models\User;
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
    
    /**
     * Obtenir l'auteur de l'article basé sur le champ writer
     */
    public function author()
    {
        return User::where('name', $this->writer)->first();
    }
}
