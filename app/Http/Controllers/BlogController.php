<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use HTMLPurifier;
use HTMLPurifier_Config;

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

class BlogController extends Controller
{
    public function index()
    {
        $articles = Blog::all();
        return response()->json([
            'Liste des articles' => $articles
        ]);
    }

    public function indextwo()
    {
        $articles = Blog::all();
        $articles = $articles->map(function($article) {
            if ($article->image) {
                
                if (!str_starts_with($article->image, 'http')) {
                    // Nettoyer le chemin de l'image
                    $cleanPath = str_replace('images/', '', $article->image);
                    $article->image = url('images/' . $cleanPath);
                }
            }
            return $article;
        });
        return response()->json([
            'blogs' => $articles
        ]);
    }

    /**
 * Affiche un article spécifique par son ID
 *
 * @param  int  $id
 * @return \Illuminate\Http\JsonResponse
 */
public function show($id)
{
    
    $article = Blog::find($id); // Utilisez find() au lieu de where()->first()

    if (!$article) {
        return response()->json([
            "message" => "Article non trouvé"
        ], 404);
    }

    if ($article->image && !str_starts_with($article->image, 'http')) {
        $article->image = url('images/' . $article->image);
    }

    return response()->json([
        "article" => $article
    ]);
}

    public function blogDetail($slug)
    {
        $article = Blog::where('slug', $slug)->first();

        if (!$article) {
            return response()->json([
                "message" => "Article non trouvé"
            ], 404);
        }

        if ($article->image) {
            if (!str_starts_with($article->image, 'http')) {
                // Nettoyer le chemin de l'image
                $cleanPath = str_replace('images/', '', $article->image);
                $article->image = url('images/' . $cleanPath);
            }
        }

        return response()->json([
            "article" => $article
        ]);
    }

    public function blogDetailPublic($slug)
    {
        $article = Blog::where('slug', $slug)
                       ->where('statut', 'validé') // Seulement les articles validés
                       ->first();

        if (!$article) {
            return response()->json([
                "message" => "Article non trouvé"
            ], 404);
        }

        if ($article->image) {
            if (!str_starts_with($article->image, 'http')) {
                // Nettoyer le chemin de l'image
                $cleanPath = str_replace('images/', '', $article->image);
                $article->image = url('images/' . $cleanPath);
            }
        }

        return response()->json([
            "article" => $article
        ]);
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

        // Traitement de l'image
        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
        }

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $contenu_propre = $purifier->purify($request->contenu);
        $article = Blog::create([
            'titre' => $request->titre,
            'contenu' => $contenu_propre, // Nettoyage du HTML
            'writer' => $request->writer,
            'resume' => $request->resume,
            'slug' => Str::slug($request->titre),
            'image' => $imageName ? '/images/' . $imageName : null,
            'statut' => 'en cours', // Statut par défaut
            'note' => null, // Pas de note initiale
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

        // Mise à jour standard
        if ($request->has('titre')) {
            $article->titre = $request->titre;
            $article->slug = Str::slug($request->titre);
        }

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $contenu_propre = $purifier->purify($request->contenu);
        
        if ($request->has('contenu')) {
            $article->contenu = $contenu_propre;
        }

        // Gestion du statut et des notes (seulement pour admin)
        if ($request->user()->role === 'super_admin') {
            if ($request->has('statut')) {
                $article->statut = $request->statut;
            }
            if ($request->has('note')) {
                $article->note = $request->note;
            }
        }

        // Gestion de l'image
        if ($request->hasFile('image')) {
            // Suppression ancienne image
            if ($article->image) {
                $oldPath = public_path($article->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $article->image = '/images/' . $imageName;
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

    public function destroy($id)
    {
        $article = Blog::findOrFail($id);

        // Suppression de l'image
        if ($article->image) {
            $imagePath = public_path($article->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $article->delete();

        return response()->json([
            "message" => "Article supprimé avec succès"
        ]);
    }
}