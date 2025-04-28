<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:8|confirmed',
            'image' => 'nullable|image|max:2048' // 2MB max
        ]);

        // Vérifier le mot de passe actuel si fourni
        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Le mot de passe actuel est incorrect'], 422);
            }
        }

        // Gérer l'upload de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($user->image_path) {
                $oldPath = public_path($user->image_path);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $image = $request->file('image');
            $imageName = 'profile_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/profiles'), $imageName);
            $user->image_path = 'profiles/' . $imageName;
        }

        // Mettre à jour les informations de base
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? $user->phone;
        $user->bio = $validated['bio'] ?? $user->bio;

        // Mettre à jour le mot de passe si fourni
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }
}
