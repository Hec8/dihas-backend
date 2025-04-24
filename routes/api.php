<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Blog;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/csrf-token', function() {
    return response()->json(['token' => csrf_token()]);
});

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'store']);
Route::get('/newsletter', [NewsletterController::class, 'index'])->middleware('auth'); // admin
Route::delete('/newsletter/delete/{id}', [NewsletterController::class, 'destroy'])->middleware('auth'); // admin

// Contact
Route::get('/contact', [ContactController::class, 'index'])->middleware('auth');
Route::post('/contact/create', [ContactController::class, 'store']); // admin
Route::delete('/contact/delete/{id}', [ContactController::class, 'destroy'])->middleware('auth'); //admin
Route::put('/contact/{id}/mark-as-read', [ContactController::class, 'markAsRead']);

// Blog
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/guest', [BlogController::class, 'indextwo']);
Route::get('/blog/edit/{id}', [BlogController::class, 'show']);
Route::get('/blog/view/{slug}', [BlogController::class, 'blogDetail']); //prévisualiser l'article dans une page pour l'amin et le content-creator
Route::get('/blog/guest/{slug}', [BlogController::class, 'blogDetailPublic']); // récupérer l'article pour les visiteurs
Route::put('/blog/{id}', [BlogController::class, 'update']); // admin modifier l'article
Route::post('/blog/create', [BlogController::class, 'store'])->middleware('auth'); // admin
Route::delete('/blog/delete/{id}', [BlogController::class, 'destroy'])->middleware('auth'); //admin

//Employes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/employees', [RegisteredUserController::class, 'index']);
    Route::post('/employees', [RegisteredUserController::class, 'store']);
    Route::delete('/employees/{id}', [RegisteredUserController::class, 'destroy']);
});

// Pour les stats
Route::get('/blog/stats', [BlogController::class, 'stats']);
Route::post('/blog/validate/{id}', [BlogController::class, 'validateArticle']);


// Pour la validation
Route::put('/api/blog/validate/{id}', function(Request $request, $id) {
    $blog = Blog::findOrFail($id);
    $blog->update($request->only(['statut', 'note']));
    return response()->json(['message' => 'Statut mis à jour']);
});