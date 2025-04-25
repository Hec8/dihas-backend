<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // Pour les URLs
            $table->string('title');
            $table->string('homepage_image')->nullable(); // Chemin vers l'image principale
            $table->text('short_description')->nullable();
            $table->string('logo')->nullable(); // Chemin vers le logo spécifique
            $table->longText('long_description')->nullable();
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->string('industry')->nullable();
            $table->text('monetization')->nullable();
            $table->string('estimated_profit')->nullable();
            $table->string('estimated_revenue')->nullable();
            $table->json('detail_images')->nullable(); // Stockera un tableau de chemins d'images
            $table->longText('why_buy')->nullable();
            $table->longText('main_features')->nullable();
            $table->longText('admin_features')->nullable();
            $table->longText('economic_model')->nullable();
            $table->longText('data_security')->nullable();
            $table->date('last_updated')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
