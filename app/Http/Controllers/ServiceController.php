<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //visitors
    public function index()
    {
        $services = Service::where('is_active', true)->get();
        return response()->json([
            'data' => $services
        ], 200, [], JSON_PRETTY_PRINT);
    }

    //admins
    public function index_two()
    {
        $services = Service::all();
        return response()->json([
            'Liste des services' => $services
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // Traitement de l'icone
        $imageName = null;
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $imageName = time() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('images/services'), $imageName);
        }

        $data = [
            'title' => $request->title,
            'content' => $request->content,
            'slug' => Str::slug($request->title),
            'is_active' => $request->is_active ?? true,
            'icon' => $imageName ? '/images/services/' . $imageName : null,
        ];

        Service::create($data);

        return response()->json([
            'message' => 'Service créé avec succès'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return response()->json($service);
    }

    /**
     * Update the specified resource in storage.
     */

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'title' => 'sometimes|required|string|max:255',
        'content' => 'sometimes|required|string',
        'is_active' => 'sometimes|boolean',
        'icon' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
    ]);

    $service = Service::findOrFail($id);

    // Mise à jour du slug si le titre change
    if ($request->has('title') && $request->title !== $service->title) {
        $validated['slug'] = Str::slug($request->title);
    }

    // Gestion de l'icône
    $imageName = null;
    if ($request->hasFile('icon')) {
        // Supprimer l'ancienne icône si elle existe
        if ($service->icon) {
            $oldImagePath = public_path($service->icon);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Enregistrer la nouvelle icône
        $icon = $request->file('icon');
        $imageName = time() . '.' . $icon->getClientOriginalExtension();
        $icon->move(public_path('images/services'), $imageName);
        $validated['icon'] = '/images/services/' . $imageName;
    } else {
        // Conserver l'icône existante si aucune nouvelle n'est fournie
        $validated['icon'] = $service->icon;
    }

    $service->update($validated);

    return response()->json($service);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(null, 204);
    }
}
