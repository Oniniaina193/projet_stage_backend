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
        Schema::create('medecins', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('specialite', 150);
            $table->string('numero_ordre', 50)->unique();
            $table->string('telephone', 20);
            $table->string('email', 150)->unique();
            $table->text('adresse');
            $table->boolean('actif')->default(true);
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['nom', 'prenom']);
            $table->index('specialite');
            $table->index('numero_ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medecins');
    }
};