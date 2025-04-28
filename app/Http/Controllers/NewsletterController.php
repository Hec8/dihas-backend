<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use App\Models\User;
use App\Notifications\NewsletterSubscription;
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
            'email' => 'required|email|unique:newsletters'
        ]);

        $subscriber = Newsletter::create([
            'email' => $request->email
        ]);

        // Notifier l'administrateur
        $admin = User::where('role', 'super_admin')->first();
        if ($admin) {
            $admin->notify(new NewsletterSubscription($subscriber));
        }

        return response()->json([
            'message' => 'Inscription réussie à la newsletter'
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
