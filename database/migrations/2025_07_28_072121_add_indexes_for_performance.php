<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('medicaments', function (Blueprint $table) {
            // Index pour les recherches par nom (ILIKE)
            $table->index('nom', 'idx_medicaments_nom');
            
            // Index pour le filtrage par statut
            $table->index('status', 'idx_medicaments_status');
            
            // Index pour les requêtes de stock faible
            $table->index('stock', 'idx_medicaments_stock');
            
            // Index composé pour les stats prix/stock
            $table->index(['prix', 'stock'], 'idx_medicaments_prix_stock');
            
            // Index pour les tris par date
            $table->index('created_at', 'idx_medicaments_created_at');
            $table->index('updated_at', 'idx_medicaments_updated_at');
        });

        Schema::table('medecins', function (Blueprint $table) {
            // Index pour les recherches par nom/prénom
            $table->index('nom', 'idx_medecins_nom');
            $table->index('prenom', 'idx_medecins_prenom');
            
            // Index pour le filtrage par spécialité
            $table->index('specialite', 'idx_medecins_specialite');
            
            // Index pour les tris par date
            $table->index('created_at', 'idx_medecins_created_at');
        });

        // Si la table ordonnances existe
        if (Schema::hasTable('ordonnances')) {
            Schema::table('ordonnances', function (Blueprint $table) {
                // Index pour les jointures avec médecins
                $table->index('id_medecin', 'idx_ordonnances_medecin');
                
                // Index pour les requêtes par date
                $table->index('date_ordonnance', 'idx_ordonnances_date');
                $table->index('created_at', 'idx_ordonnances_created_at');
                
                // Index pour le filtrage par statut
                $table->index('status', 'idx_ordonnances_status');
                
                // Index pour les calculs de chiffre d'affaires
                $table->index(['created_at', 'total'], 'idx_ordonnances_ca');
            });
        }
    }

    public function down()
    {
        Schema::table('medicaments', function (Blueprint $table) {
            $table->dropIndex('idx_medicaments_nom');
            $table->dropIndex('idx_medicaments_status');
            $table->dropIndex('idx_medicaments_stock');
            $table->dropIndex('idx_medicaments_prix_stock');
            $table->dropIndex('idx_medicaments_created_at');
            $table->dropIndex('idx_medicaments_updated_at');
        });

        Schema::table('medecins', function (Blueprint $table) {
            $table->dropIndex('idx_medecins_nom');
            $table->dropIndex('idx_medecins_prenom');
            $table->dropIndex('idx_medecins_specialite');
            $table->dropIndex('idx_medecins_created_at');
        });

        if (Schema::hasTable('ordonnances')) {
            Schema::table('ordonnances', function (Blueprint $table) {
                $table->dropIndex('idx_ordonnances_medecin');
                $table->dropIndex('idx_ordonnances_date');
                $table->dropIndex('idx_ordonnances_created_at');
                $table->dropIndex('idx_ordonnances_status');
                $table->dropIndex('idx_ordonnances_ca');
            });
        }
    }
};