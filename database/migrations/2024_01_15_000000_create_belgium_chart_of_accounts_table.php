<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour le Plan Comptable Minimum Normalisé (PCMN) Belgique
 *
 * Structure basée sur le PCMN officiel belge avec 7 classes principales
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('belgium_chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 20)->unique()->index();
            $table->string('account_name');
            $table->string('account_name_nl')->nullable(); // Nom néerlandais
            $table->string('account_name_en')->nullable(); // Nom anglais
            $table->text('description')->nullable();
            $table->enum('type', [
                'asset',           // Actif
                'liability',       // Passif
                'equity',          // Capitaux propres
                'revenue',         // Produits
                'expense',         // Charges
                'special'          // Comptes spéciaux
            ]);
            $table->enum('class', [
                '1', // Classe 1: Capitaux propres, provisions et dettes à plus d'un an
                '2', // Classe 2: Frais d'établissement, actifs immobilisés et créances à plus d'un an
                '3', // Classe 3: Stock et commandes en cours d'exécution
                '4', // Classe 4: Créances et dettes à un an au plus
                '5', // Classe 5: Placements de trésorerie et valeurs disponibles
                '6', // Classe 6: Charges
                '7', // Classe 7: Produits
                '0', // Classe 0: Droits et engagements hors bilan
            ]);
            $table->string('parent_account', 20)->nullable()->index();
            $table->integer('level')->default(1); // Niveau hiérarchique
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->boolean('allow_direct_posting')->default(true);
            $table->json('tax_info')->nullable(); // Info TVA (taux, type)
            $table->json('reporting_info')->nullable(); // Info pour BNB reporting
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('class');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('belgium_chart_of_accounts');
    }
};
