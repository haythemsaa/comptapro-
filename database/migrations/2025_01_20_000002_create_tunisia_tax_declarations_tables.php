<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des déclarations de TVA
        Schema::create('vat_declarations_tunisia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->enum('period_type', ['monthly', 'quarterly']); // Mensuel ou trimestriel
            $table->integer('month')->nullable(); // Pour mensuel
            $table->integer('quarter')->nullable(); // Pour trimestriel (1-4)
            $table->integer('year');
            $table->date('period_start');
            $table->date('period_end');

            // TVA collectée (à payer)
            $table->decimal('sales_19_ht', 15, 3)->default(0); // CA HT à 19%
            $table->decimal('sales_19_vat', 15, 3)->default(0); // TVA à 19%
            $table->decimal('sales_13_ht', 15, 3)->default(0); // CA HT à 13%
            $table->decimal('sales_13_vat', 15, 3)->default(0); // TVA à 13%
            $table->decimal('sales_7_ht', 15, 3)->default(0); // CA HT à 7%
            $table->decimal('sales_7_vat', 15, 3)->default(0); // TVA à 7%
            $table->decimal('sales_export_ht', 15, 3)->default(0); // Exportations (0%)
            $table->decimal('sales_exempt_ht', 15, 3)->default(0); // Ventes exonérées

            $table->decimal('total_sales_ht', 15, 3); // Total CA HT
            $table->decimal('total_vat_collected', 15, 3); // Total TVA collectée

            // TVA déductible (à récupérer)
            $table->decimal('purchases_vat_immobilisations', 15, 3)->default(0);
            $table->decimal('purchases_vat_goods', 15, 3)->default(0);
            $table->decimal('purchases_vat_services', 15, 3)->default(0);
            $table->decimal('purchases_vat_import', 15, 3)->default(0);
            $table->decimal('total_vat_deductible', 15, 3);

            // Régularisations
            $table->decimal('vat_adjustments', 15, 3)->default(0);
            $table->decimal('vat_credit_previous', 15, 3)->default(0); // Crédit mois précédent

            // Résultat
            $table->decimal('vat_to_pay', 15, 3)->default(0); // TVA à payer
            $table->decimal('vat_credit', 15, 3)->default(0); // Crédit de TVA

            // Dates et statut
            $table->date('due_date'); // 28 du mois suivant
            $table->date('submission_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'paid', 'late', 'amended'])->default('draft');

            // Télédéclaration
            $table->string('teledeclaration_reference')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('declaration_data')->nullable();
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'year', 'month']);
            $table->index('status');
        });

        // Table des déclarations d'Impôt sur les Sociétés (IS)
        Schema::create('corporate_tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->integer('fiscal_year');
            $table->date('fiscal_year_start');
            $table->date('fiscal_year_end');

            // Résultat comptable
            $table->decimal('accounting_profit', 15, 3)->default(0);
            $table->decimal('accounting_loss', 15, 3)->default(0);

            // Réintégrations fiscales
            $table->decimal('reintegrations_total', 15, 3)->default(0);
            $table->json('reintegrations_details')->nullable();

            // Déductions fiscales
            $table->decimal('deductions_total', 15, 3)->default(0);
            $table->json('deductions_details')->nullable();

            // Résultat fiscal
            $table->decimal('taxable_profit', 15, 3)->default(0);
            $table->decimal('tax_loss', 15, 3)->default(0);
            $table->decimal('tax_loss_carryforward', 15, 3)->default(0); // Report déficitaire

            // Calcul de l'IS
            $table->decimal('tax_rate', 5, 2)->default(15); // 15% ou 25% ou 35%
            $table->decimal('corporate_tax_due', 15, 3)->default(0);

            // Acomptes provisionnels payés
            $table->decimal('advance_payment_q1', 15, 3)->default(0);
            $table->decimal('advance_payment_q2', 15, 3)->default(0);
            $table->decimal('advance_payment_q3', 15, 3)->default(0);
            $table->decimal('total_advance_payments', 15, 3)->default(0);

            // Retenues à la source subies
            $table->decimal('withholding_tax', 15, 3)->default(0);

            // Résultat final
            $table->decimal('net_tax_to_pay', 15, 3)->default(0);
            $table->decimal('tax_credit', 15, 3)->default(0);

            // Dates et statut
            $table->date('due_date'); // 25 mars N+1
            $table->date('submission_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'paid', 'late', 'amended'])->default('draft');

            $table->string('teledeclaration_reference')->nullable();
            $table->json('declaration_data')->nullable();
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'fiscal_year']);
            $table->index('status');
        });

        // Table des acomptes provisionnels IS
        Schema::create('corporate_tax_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corporate_tax_declaration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('advance_number')->unique();
            $table->integer('fiscal_year');
            $table->integer('quarter'); // 1, 2, 3
            $table->date('due_date'); // Fin juin, septembre, décembre
            $table->decimal('amount_due', 15, 3);
            $table->decimal('amount_paid', 15, 3)->default(0);
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'late'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'fiscal_year', 'quarter']);
        });

        // Table TFP (Taxe de Formation Professionnelle) - 2% ou 1%
        Schema::create('tfp_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->integer('month');
            $table->integer('year');

            $table->decimal('total_gross_salaries', 15, 3); // Base de calcul
            $table->decimal('tfp_rate', 5, 2)->default(2); // 2% (industrie) ou 1% (autres)
            $table->decimal('tfp_amount', 15, 3);

            $table->date('due_date'); // 28 du mois suivant avec TVA
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'paid', 'late'])->default('draft');
            $table->string('payment_reference')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
        });

        // Table TCL (Taxe sur les établissements à caractère industriel)
        Schema::create('tcl_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->integer('year'); // Déclaration annuelle

            $table->decimal('taxable_salaries', 15, 3); // Salaires de l'année précédente
            $table->decimal('tcl_rate', 5, 2)->default(0.2); // 0.2% sur salaires
            $table->decimal('tcl_amount', 15, 3);

            $table->date('due_date'); // Avant fin janvier N+1
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'paid', 'late'])->default('draft');
            $table->string('payment_reference')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['company_id', 'year']);
        });

        // Table FOPROLOS (Fonds de Promotion des Logements Salariés)
        Schema::create('foprolos_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->integer('month');
            $table->integer('year');

            $table->decimal('total_gross_salaries', 15, 3);
            $table->decimal('foprolos_rate', 5, 2)->default(1); // 1% sur salaires
            $table->decimal('foprolos_amount', 15, 3);

            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'paid', 'late'])->default('draft');
            $table->string('payment_reference')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
        });

        // Table des retenues à la source (IRPP, honoraires, etc.)
        Schema::create('withholding_tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->integer('month');
            $table->integer('year');
            $table->enum('withholding_type', [
                'salaries', // Retenue sur salaires
                'honoraires', // Retenue sur honoraires (15%)
                'services', // Retenue sur services (1.5%)
                'rent', // Retenue sur loyers (15%)
                'commissions' // Retenue sur commissions
            ]);

            $table->decimal('total_paid_amount', 15, 3); // Montant brut payé
            $table->decimal('withholding_rate', 5, 2); // Taux de retenue
            $table->decimal('withholding_amount', 15, 3); // Montant retenu

            $table->integer('beneficiaries_count')->default(0);
            $table->json('beneficiaries_details')->nullable();

            $table->date('due_date'); // 28 du mois suivant
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'paid', 'late'])->default('draft');
            $table->string('payment_reference')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'year', 'month']);
        });

        // Table du calendrier fiscal tunisien
        Schema::create('tax_calendar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('tax_type'); // VAT, IS, CNSS, TFP, etc.
            $table->string('declaration_type'); // monthly, quarterly, annual
            $table->date('due_date');
            $table->integer('month')->nullable();
            $table->integer('quarter')->nullable();
            $table->integer('year');
            $table->string('description');
            $table->enum('status', ['upcoming', 'due_soon', 'overdue', 'completed'])->default('upcoming');
            $table->foreignId('related_declaration_id')->nullable(); // ID de la déclaration associée
            $table->string('related_declaration_type')->nullable(); // Type du modèle
            $table->timestamps();

            $table->index(['company_id', 'due_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_calendar');
        Schema::dropIfExists('withholding_tax_declarations');
        Schema::dropIfExists('foprolos_declarations');
        Schema::dropIfExists('tcl_declarations');
        Schema::dropIfExists('tfp_declarations');
        Schema::dropIfExists('corporate_tax_advances');
        Schema::dropIfExists('corporate_tax_declarations');
        Schema::dropIfExists('vat_declarations_tunisia');
    }
};
