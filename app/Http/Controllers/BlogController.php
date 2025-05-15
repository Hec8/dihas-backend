<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Notifications\ArticleNeedsRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

class BlogController extends Controller
{
    public function index()
    {
        $articles = Blog::all();
        return response()->json(['Liste des articles' => $articles]);
    }

    public function indextwo()
    {
        $articles = Blog::where('statut', 'validé')->get();
        return response()->json(['blogs' => $articles]);
    }

    public function show($id)
    {
        $article = Blog::find($id);

        if (!$article) {
            return response()->json(["message" => "Article non trouvé"], 404);
        }

        return response()->json(["article" => $article]);
    }

    public function blogPreview($slug)
    {
        $article = Blog::where('slug', $slug)->first();

        if (!$article) {
            return response()->json(["message" => "Article non trouvé"], 404);
        }

        if (
            Auth::user()->role !== 'super_admin' &&
            Auth::user()->role !== 'content_creator' &&
            $article->writer !== Auth::user()->name
        ) {
            return response()->json(["message" => "Non autorisé à voir cet article"], 403);
        } 

        return response()->json(["article" => $article]);
    }

    public function blogDetailPublic($slug)
    {
        $article = Blog::where('slug', $slug)
                       ->where('statut', 'validé')
                       ->first();

        if (!$article) {
            return response()->json(["message" => "Article non trouvé"], 404);
        }

        return response()->json(["article" => $article]);
    }

    public function stats()
    {
        return response()->json([
            'validated' => Blog::where('statut', 'validé')->count(),
            'pending' => Blog::where('statut', 'en cours')->count(),
            'rejected' => Blog::where('statut', 'renvoyé')->count(),
            'total' => Blog::count()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'writer' => 'required|string|max:255',
            'resume' => 'required|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageUrl = null;
        $imagePublicId = null;

        if ($request->hasFile('image')) {
            // Sauvegarde temporaire du fichier
            $path = $request->file('image')->store('temp');
            $fullPath = Storage::path($path);
            
            // Upload vers Cloudinary
            $result = cloudinary()->uploadApi()->upload($fullPath, ['folder' => 'blogs']);
            
            // Récupération des informations
            $imageUrl = $result['secure_url'];
            $imagePublicId = $result['public_id'];
            
            // Suppression du fichier temporaire
            Storage::delete($path);
        }

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $contenu_propre = $purifier->purify($request->contenu);

        $article = Blog::create([
            'titre' => $request->titre,
            'contenu' => $contenu_propre,
            'writer' => $request->writer,
            'resume' => $request->resume,
            'slug' => Str::slug($request->titre),
            'image' => $imageUrl,
            'image_public_id' => $imagePublicId,
        ]);

        return response()->json([
            "message" => "Article créé avec succès",
            "article" => $article
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $article = Blog::findOrFail($id);

        $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'contenu' => 'sometimes|required|string',
            'writer' => 'sometimes|required|string|max:255',
            'resume' => 'sometimes|required|string|max:500',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'statut' => 'sometimes|in:en cours,validé,renvoyé',
            'note' => 'nullable|string|max:1000'
        ]);

        if ($request->has('titre')) {
            $article->titre = $request->titre;
            $article->slug = Str::slug($request->titre);
        }

        if ($request->has('contenu')) {
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
            $article->contenu = $purifier->purify($request->contenu);
        }

        if ($request->user()->role === 'super_admin') {
            if ($request->has('statut')) $article->statut = $request->statut;
            if ($request->has('note')) $article->note = $request->note;
        }

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si présente
            if ($article->image_public_id) {
                cloudinary()->uploadApi()->destroy($article->image_public_id);
            }

            // Sauvegarde temporaire du fichier
            $path = $request->file('image')->store('temp');
            $fullPath = Storage::path($path);
            
            // Upload vers Cloudinary
            $result = cloudinary()->uploadApi()->upload($fullPath, ['folder' => 'blogs']);
            
            // Récupération des informations
            $article->image = $result['secure_url'];
            $article->image_public_id = $result['public_id'];
            
            // Suppression du fichier temporaire
            Storage::delete($path);
        }

        $article->save();

        return response()->json([
            "message" => "Article mis à jour avec succès",
            "article" => $article
        ]);
    }

    public function validateArticle(Request $request, $id)
    {
        $article = Blog::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:validé,renvoyé',
            'note' => 'required_if:statut,renvoyé|nullable|string|max:1000'
        ]);

        $article->update([
            'statut' => $request->statut,
            'note' => $request->note
        ]);

        return response()->json([
            "message" => "Statut de l'article mis à jour",
            "article" => $article
        ]);
    }

    public function requestRevision(Request $request, $id)
    {
        $article = Blog::findOrFail($id);
        $article->update(['statut' => 'renvoyé']);

        $article->author->notify(new ArticleNeedsRevision($article));

        return response()->json([
            'message' => 'Demande de révision envoyée'
        ]);
    }

    public function destroy($id)
    {
        $article = Blog::findOrFail($id);

        // Supprimer l’image sur Cloudinary si présente
        if ($article->image_public_id) {
            Cloudinary::destroy($article->image_public_id);
        }

        $article->delete();

        return response()->json([
            "message" => "Article supprimé avec succès"
        ]);
    }
}