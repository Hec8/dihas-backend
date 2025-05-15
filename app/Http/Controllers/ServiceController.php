<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    // visiteurs
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        return response()->json([
            'data' => $services
        ], 200, [], JSON_PRETTY_PRINT);
    }

    // admins
    public function index_two()
    {
        $services = Service::all();
        return response()->json([
            'Liste des services' => $services
        ]);
    }

    // création d’un service
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $iconUrl = null;
        $iconPublicId = null;

        if ($request->hasFile('icon')) {
            // Sauvegarde temporaire du fichier
            $path = $request->file('icon')->store('temp');
            $fullPath = Storage::path($path);
            
            // Upload vers Cloudinary
            $result = cloudinary()->uploadApi()->upload($fullPath, ['folder' => 'services']);
            
            // Récupération des informations
            $iconUrl = $result['secure_url'];
            $iconPublicId = $result['public_id'];
            
            // Suppression du fichier temporaire
            Storage::delete($path);
        }

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'slug' => Str::slug($request->title),
            'is_active' => $request->is_active ?? true,
            'icon' => $iconUrl,
            'public_icon_id' => $iconPublicId,
        ];

        Service::create($data);

        return response()->json([
            'message' => 'Service créé avec succès'
        ]);
    }

    // afficher un service
    public function show(Service $service)
    {
        return response()->json($service);
    }

    // mise à jour
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'is_active' => 'sometimes|boolean',
            'icon' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $service = Service::findOrFail($id);

        // si le titre change, on change le slug
        if ($request->has('title') && $request->title !== $service->title) {
            $validated['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('icon')) {
            // suppression de l’ancienne image
            if ($service->public_icon_id) {
                cloudinary()->uploadApi()->destroy($service->public_icon_id);
            }

            // Sauvegarde temporaire du fichier
            $path = $request->file('icon')->store('temp');
            $fullPath = Storage::path($path);
            
            // Upload vers Cloudinary
            $result = cloudinary()->uploadApi()->upload($fullPath, ['folder' => 'services']);
            
            // Récupération des informations
            $service->icon = $result['secure_url'];
            $service->public_icon_id = $result['public_id'];
            
            // Suppression du fichier temporaire
            Storage::delete($path);
        } else {
            $validated['icon'] = $service->icon;
        }

        $service->update($validated);

        return response()->json($service);
    }

    // suppression
    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        if ($service->public_icon_id) {
            cloudinary()->uploadApi()->destroy($service->public_icon_id);
        }

        $service->delete();

        return response()->json([
            "message" => "Service supprimé avec succès"
        ]);
    }
}