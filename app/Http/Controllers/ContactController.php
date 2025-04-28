<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        //
        $messages = Contact::orderBy('created_at', 'desc')
        ->get();
    
    return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'telephone' => 'required|string|max:255', 
            'message' => 'required|string'
        ]);

        $contact = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'message' => $request->message
        ]);

        // Notifier l'administrateur
        $admin = User::where('role', 'super_admin')->first();
        if ($admin) {
            $admin->notify(new NewContactMessage($contact));
        }

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'data' => $contact
        ], 201);
    }

    public function markAsRead($id)
    {
        $message = Contact::findOrFail($id);
        $message->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Message marqué comme lu'
        ]);
    }

    public function destroy($id)
    {
        // Récupérer le message
        $message = Contact::find($id);

        if (!$message) {
            return response()->json([
                "message" => "Message non trouvé"
            ], 404);
        }

        // Supprimer le message
        $message->delete();

        return response()->json([
            "message" => "Message supprimé avec succès"
        ]);
    }
}
