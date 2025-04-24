<?php

namespace App\Http\Controllers;

use App\Models\Contact;
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
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'telephone' => 'sometimes'
        ]);

        $message = Contact::create($request->all());

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'data' => $message
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
