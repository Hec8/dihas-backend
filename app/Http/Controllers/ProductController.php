<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage; // pour gérer les fichiers images
use Illuminate\Support\Facades\Validator; // pour valider les données entrantes
use Illuminate\Support\Str; // pour générer des URL uniques

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retourne tous les produits publiés, paginés (15 par page par défaut)
        $products = Product::where('is_published', true)->latest()->paginate(15);
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'homepage_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // max 2MB
            'short_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:1024', // max 1MB
            'long_description' => 'nullable|string',
            'location' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'monetization' => 'nullable|string',
            'estimated_profit' => 'nullable|string|max:100',
            'estimated_revenue' => 'nullable|string|max:100',
            'detail_images' => 'nullable|array', // S'assurer que c'est un tableau
            'detail_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Valider chaque image du tableau
            'why_buy' => 'nullable|string',
            'main_features' => 'nullable|string',
            'admin_features' => 'nullable|string',
            'economic_model' => 'nullable|string',
            'data_security' => 'nullable|string',
            'last_updated' => 'nullable|date',
            'is_published' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Erreur de validation
        }

        $validatedData = $validator->validated();

        // Générer le slug
        $validatedData['slug'] = Str::slug($validatedData['title']);
        // (Optionnel : ajouter une logique pour rendre le slug unique si le titre peut se répéter)
        // $count = Product::where('slug', 'LIKE', $validatedData['slug'].'%')->count();
        // if($count > 0) { $validatedData['slug'] = $validatedData['slug'].'-'.($count + 1); }


        // Gérer l'upload de l'image principale
        if ($request->hasFile('homepage_image')) {
            $validatedData['homepage_image'] = $request->file('homepage_image')->store('products/homepage', 'public');
        }

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            $validatedData['logo'] = $request->file('logo')->store('products/logos', 'public');
        }

         // Gérer l'upload des images de détail
         if ($request->hasFile('detail_images')) {
            $detailImagePaths = [];
            foreach ($request->file('detail_images') as $file) {
                $detailImagePaths[] = $file->store('products/details', 'public');
            }
            $validatedData['detail_images'] = $detailImagePaths; // Le cast JSON se fera via le modèle
        }

        $product = Product::create($validatedData);

        return response()->json($product, 201); // 201 Created
    }

    /**
     * Display the specified resource.
     * On utilisera le slug pour récupérer le produit pour les urls plus jolies
    */
    public function show(string $id)//
    {
        $product = Product::where('id', $id)
        ->where('is_published', true)
        ->firstOrFail();
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) // garder l'id ici car la route par défaut gardera l'ID 
    {
        $product = Product::findOrFail($id);

         $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255', // 'sometimes' = valider si présent
            'homepage_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'short_description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:1024',
            'long_description' => 'nullable|string',
            'location' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'monetization' => 'nullable|string',
            'estimated_profit' => 'nullable|string|max:100',
            'estimated_revenue' => 'nullable|string|max:100',
            'detail_images' => 'nullable|array',
            'detail_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'why_buy' => 'nullable|string',
            'main_features' => 'nullable|string',
            'admin_features' => 'nullable|string',
            'economic_model' => 'nullable|string',
            'data_security' => 'nullable|string',
            'last_updated' => 'nullable|date',
            'is_published' => 'sometimes|boolean',
            // Ne pas valider l'unicité du slug ici directement, on le gère si le titre change
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

         // Regénérer le slug si le titre a changé
        if ($request->has('title') && $product->title !== $validatedData['title']) {
             $validatedData['slug'] = Str::slug($validatedData['title']);
             // (Ajouter logique d'unicité si nécessaire)
        }

        // Gérer la mise à jour de l'image principale
        if ($request->hasFile('homepage_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->homepage_image) {
                Storage::disk('public')->delete($product->homepage_image);
            }
            $validatedData['homepage_image'] = $request->file('homepage_image')->store('products/homepage', 'public');
        }

        // Gérer la mise à jour du logo
        if ($request->hasFile('logo')) {
            if ($product->logo) {
                Storage::disk('public')->delete($product->logo);
            }
            $validatedData['logo'] = $request->file('logo')->store('products/logos', 'public');
        }

        // Gérer la mise à jour des images de détail (remplacement complet ici, logique plus complexe possible)
         if ($request->hasFile('detail_images')) {
             // Supprimer les anciennes images
             if ($product->detail_images && is_array($product->detail_images)) {
                 foreach ($product->detail_images as $oldImage) {
                    if($oldImage) { // Vérifier si le chemin n'est pas null/vide
                        Storage::disk('public')->delete($oldImage);
                    }
                 }
             }
             // Uploader les nouvelles
             $detailImagePaths = [];
             foreach ($request->file('detail_images') as $file) {
                 $detailImagePaths[] = $file->store('products/details', 'public');
             }
             $validatedData['detail_images'] = $detailImagePaths;
            } elseif ($request->has('detail_images') && is_null($request->input('detail_images'))) {
                // Gérer le cas où on veut explicitement vider les images de détail
                if ($product->detail_images && is_array($product->detail_images)) {
                    foreach ($product->detail_images as $oldImage) {
                       if($oldImage) {
                           Storage::disk('public')->delete($oldImage);
                       }
                    }
                }
                $validatedData['detail_images'] = null;
            }
   
   
           $product->update($validatedData);
   
           return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Supprimer les images associées avant de supprimer le produit
        if ($product->homepage_image) {
            Storage::disk('public')->delete($product->homepage_image);
        }
        if ($product->logo) {
            Storage::disk('public')->delete($product->logo);
        }
        if ($product->detail_images && is_array($product->detail_images)) {
            foreach ($product->detail_images as $imagePath) {
                if($imagePath) {
                   Storage::disk('public')->delete($imagePath);
                }
            }
        }

        $product->delete();

        return response()->json('produit supprimé avec succès', 204); // 204 No Content
    }
}
