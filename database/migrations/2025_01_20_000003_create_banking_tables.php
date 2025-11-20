<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des comptes bancaires
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('iban')->nullable();
            $table->string('bic_swift')->nullable();
            $table->string('bank_name');
            $table->string('branch')->nullable();
            $table->string('currency', 3)->default('TND');
            $table->string('account_code')->nullable(); // Code comptable (ex: 5121)
            $table->decimal('current_balance', 15, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('bank_details')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        // Table des relevés bancaires
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->string('statement_number')->nullable();
            $table->date('statement_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance', 15, 3);
            $table->decimal('ending_balance', 15, 3);
            $table->decimal('total_debits', 15, 3)->default(0);
            $table->decimal('total_credits', 15, 3)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->string('file_path')->nullable();
            $table->enum('status', ['imported', 'reconciled', 'archived'])->default('imported');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'bank_account_id']);
            $table->index('statement_date');
        });

        // Table des transactions bancaires
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_statement_id')->nullable()->constrained()->nullOnDelete();
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->string('reference')->nullable();
            $table->text('description');
            $table->decimal('amount', 15, 3);
            $table->decimal('balance', 15, 3')->nullable();
            $table->enum('type', ['debit', 'credit']);
            $table->string('category')->nullable();

            // Rapprochement
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('reconciliation_type', ['manual', 'auto', 'ai_suggested'])->nullable();
            $table->decimal('reconciliation_confidence', 3, 2)->nullable();

            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'bank_account_id']);
            $table->index('transaction_date');
            $table->index('is_reconciled');
        });

        // Table de l'historique de rapprochement
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->string('reconciliation_number')->unique();
            $table->date('reconciliation_date');
            $table->date('period_start');
            $table->date('period_end');

            // Soldes
            $table->decimal('opening_balance_accounting', 15, 3);
            $table->decimal('opening_balance_bank', 15, 3);
            $table->decimal('ending_balance_accounting', 15, 3);
            $table->decimal('ending_balance_bank', 15, 3);
            $table->decimal('difference', 15, 3);

            // Statistiques
            $table->integer('matched_count')->default(0);
            $table->integer('unmatched_bank_count')->default(0);
            $table->integer('unmatched_accounting_count')->default(0);
            $table->decimal('matched_amount', 15, 3)->default(0);

            $table->enum('status', ['draft', 'completed', 'approved'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->json('reconciliation_data')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'bank_account_id']);
            $table->index('reconciliation_date');
        });

        // Table des règles de rapprochement automatique
        Schema::create('bank_reconciliation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rule_name');
            $table->text('description')->nullable();

            // Conditions
            $table->enum('match_type', ['description', 'amount', 'reference', 'combined']);
            $table->string('pattern')->nullable();
            $table->decimal('amount_min', 15, 3)->nullable();
            $table->decimal('amount_max', 15, 3)->nullable();
            $table->boolean('is_regex')->default(false);

            // Actions
            $table->string('account_code')->nullable();
            $table->string('category')->nullable();
            $table->json('auto_create_entry')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('match_count')->default(0);
            $table->timestamp('last_matched_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_rules');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
    }
};
