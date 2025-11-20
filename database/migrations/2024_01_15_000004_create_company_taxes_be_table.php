<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_taxes_be', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('fiscal_year');
            $table->decimal('accounting_profit', 15, 2);
            $table->decimal('deductible_expenses', 15, 2)->default(0);
            $table->decimal('non_deductible_expenses', 15, 2)->default(0);
            $table->decimal('tax_exempt_income', 15, 2)->default(0);
            $table->decimal('adjustments', 15, 2)->default(0);
            $table->decimal('taxable_profit', 15, 2);
            $table->decimal('tax_rate', 5, 4)->comment('0.25 = 25%');
            $table->boolean('reduced_rate_applicable')->default(false);
            $table->decimal('tax_normal_rate', 15, 2)->default(0)->comment('IS à 25%');
            $table->decimal('tax_reduced_rate', 15, 2)->default(0)->comment('IS à 20% PME');
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('prepayments', 15, 2)->default(0)->comment('Versements anticipés');
            $table->decimal('tax_to_pay', 15, 2);
            $table->decimal('tax_credits', 15, 2)->default(0);
            $table->boolean('is_sme')->default(false)->comment('PME éligible taux réduit');
            $table->decimal('notional_interest_deduction', 15, 2)->default(0);
            $table->decimal('investment_deduction', 15, 2)->default(0);
            $table->json('details')->nullable();
            $table->enum('status', ['draft', 'validated', 'filed', 'paid'])->default('draft');
            $table->timestamp('filed_at')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'fiscal_year']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_taxes_be');
    }
};
