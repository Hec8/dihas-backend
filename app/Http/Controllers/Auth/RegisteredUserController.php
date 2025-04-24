<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\EmployeeCredentials;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index()
    {
        // Exemple : on exclut les utilisateurs ayant le rôle "super_admin"
        $employees = User::where('role', '!=', 'super_admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'employees' => $employees
        ]);
    }
    
     public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', 'max:255'], // Validation plus flexible
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role, // Rôle flexible (content_creator, editor, etc.)
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Envoi des identifiants seulement si ce n'est pas le super admin
        if ($request->role !== 'super_admin') {
            Mail::to($user->email)->send(
                new EmployeeCredentials($request->email, $request->password, $request->role)
            );
        }

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user->makeHidden(['password']) // Ne pas renvoyer le mot de passe
        ], 201);
    }

    public function destroy($id)
    {
        $employee = User::findOrFail($id);

        // Empêcher la suppression du super admin
        if ($employee->role === 'super_admin') {
            return response()->json([
                'message' => 'Impossible de supprimer un super administrateur'
            ], 403);
        }

        $employee->delete();

        return response()->json([
            'message' => 'Employé supprimé avec succès'
        ]);
    }
}