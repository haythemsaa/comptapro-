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
        // Logs des exécutions automatiques
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('workflow_type'); // auto_pilot, monthly, annual, document_processing
            $table->string('status'); // running, success, failed
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('parameters')->nullable(); // Paramètres de l'exécution
            $table->json('results')->nullable(); // Résultats de l'exécution
            $table->text('error_message')->nullable();
            $table->text('stack_trace')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'workflow_type']);
            $table->index(['company_id', 'status']);
            $table->index('started_at');
        });

        // Ajouter des colonnes aux tables existantes pour l'automatisation
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('auto_accounting_enabled')->default(false)->after('settings');
            $table->boolean('auto_validation_enabled')->default(false)->after('auto_accounting_enabled');
            $table->decimal('auto_validation_threshold', 3, 2)->default(0.90)->after('auto_validation_enabled');
            $table->json('automation_settings')->nullable()->after('auto_validation_threshold');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->boolean('created_by_ai')->default(false)->after('created_by');
            $table->decimal('ai_confidence', 3, 2)->nullable()->after('created_by_ai');
            $table->json('ai_metadata')->nullable()->after('ai_confidence');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_accounted')->default(false)->after('status');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->after('is_accounted');
            $table->boolean('imported_by_ai')->default(false)->after('journal_entry_id');
            $table->decimal('ai_confidence', 3, 2)->nullable()->after('imported_by_ai');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('is_accounted')->default(false)->after('status');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->after('is_accounted');
            $table->boolean('imported_by_ai')->default(false)->after('journal_entry_id');
            $table->decimal('ai_confidence', 3, 2)->nullable()->after('imported_by_ai');
        });

        Schema::table('vat_declarations_tunisia', function (Blueprint $table) {
            $table->boolean('generated_by_ai')->default(false)->after('status');
        });

        Schema::table('cnss_declarations', function (Blueprint $table) {
            $table->boolean('generated_by_ai')->default(false)->after('status');
        });

        Schema::table('corporate_tax_declarations', function (Blueprint $table) {
            $table->boolean('generated_by_ai')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les colonnes ajoutées
        Schema::table('corporate_tax_declarations', function (Blueprint $table) {
            $table->dropColumn('generated_by_ai');
        });

        Schema::table('cnss_declarations', function (Blueprint $table) {
            $table->dropColumn('generated_by_ai');
        });

        Schema::table('vat_declarations_tunisia', function (Blueprint $table) {
            $table->dropColumn('generated_by_ai');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['is_accounted', 'journal_entry_id', 'imported_by_ai', 'ai_confidence']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['is_accounted', 'journal_entry_id', 'imported_by_ai', 'ai_confidence']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['created_by_ai', 'ai_confidence', 'ai_metadata']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'auto_accounting_enabled',
                'auto_validation_enabled',
                'auto_validation_threshold',
                'automation_settings'
            ]);
        });

        Schema::dropIfExists('automation_logs');
    }
};
