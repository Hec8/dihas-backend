<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Blog;

Route::get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('/csrf-token', function() {
//     return response()->json(['token' => csrf_token()]);
// });

Route::middleware(['auth:sanctum'])->group(function () {
    // User Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
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

// Services
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/all', [ServiceController::class, 'index_two']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::post('/services', [ServiceController::class, 'store']);
Route::put('/services/{service}', [ServiceController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

// Blog affichage
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/guest', [BlogController::class, 'indextwo']);//liste des blogs là où il y a la photo de Axelle
Route::get('/blog/preview/{slug}', [BlogController::class, 'blogPreview']); // Prévisualisation admin
Route::get('/blog/guest/{slug}', [BlogController::class, 'blogDetailPublic']); // récupérer l'article pour les visiteurs
// Blog CRUD
Route::get('/blog/edit/{id}', [BlogController::class, 'show']); //afficher la page modification
Route::put('/blog/{id}', [BlogController::class, 'update']); // admin modifier l'article
Route::post('/blog/create', [BlogController::class, 'store'])->middleware('auth'); // admin
Route::delete('/blog/delete/{id}', [BlogController::class, 'destroy'])->middleware('auth'); //admin

//Employes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/employees', [RegisteredUserController::class, 'index']);
    Route::post('/employees', [RegisteredUserController::class, 'store']);
    Route::delete('/employees/{id}', [RegisteredUserController::class, 'destroy']);
});

//Routes pour les produits
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// Pour les stats
Route::get('/blog/stats', [BlogController::class, 'stats']);
Route::post('/blog/validate/{id}', [BlogController::class, 'validateArticle']);


// Pour la validation
Route::put('/api/blog/validate/{id}', function(Request $request, $id) {
    $blog = Blog::findOrFail($id);
    $blog->update($request->only(['statut', 'note']));
    return response()->json(['message' => 'Statut mis à jour']);
});