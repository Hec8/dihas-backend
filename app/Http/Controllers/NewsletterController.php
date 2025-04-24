<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class NewsletterController extends Controller
{
    public function index()
    {
       $subscribers = Newsletter::orderBy('created_at', 'desc')->get();
       return response()->json( $subscribers);
    }

    public function store(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    // Création dans la base de données
    $abonne = Newsletter::create([
        'email' => $request->email
    ]);

    return response()->json([
        'message' => 'Merci pour votre inscription à notre newsletter !',
        'Abonné' => $abonne
    ]);
}

    public function destroy($id)
    {
        // Récupérer l'abonné
        $subscriber = Newsletter::find($id);

        if (!$subscriber) {
            return response()->json([
                "message" => "Abonné non trouvé"
            ], 404);
        }

        // Supprimer l'abonné
        $subscriber->delete();

        return response()->json([
            "message" => "Abonné supprimé avec succès"
        ]);
    }
}
