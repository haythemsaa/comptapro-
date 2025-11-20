<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_declarations_be', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->integer('quarter')->nullable();
            $table->enum('period_type', ['monthly', 'quarterly'])->default('monthly');

            // Ventes et TVA collectée
            $table->decimal('sales_21', 12, 2)->default(0)->comment('Base CA 21% - Grille 01');
            $table->decimal('vat_21', 12, 2)->default(0)->comment('TVA 21% - Grille 02');
            $table->decimal('sales_12', 12, 2)->default(0)->comment('Base CA 12% - Grille 03');
            $table->decimal('vat_12', 12, 2)->default(0)->comment('TVA 12% - Grille 04');
            $table->decimal('sales_6', 12, 2)->default(0)->comment('Base CA 6% - Grille 05');
            $table->decimal('vat_6', 12, 2)->default(0)->comment('TVA 6% - Grille 06');
            $table->decimal('sales_0', 12, 2)->default(0)->comment('Opérations 0% - Grille 44');
            $table->decimal('sales_export', 12, 2)->default(0)->comment('Export hors UE - Grille 46');
            $table->decimal('sales_intracommunity', 12, 2)->default(0)->comment('Livraisons UE - Grille 47');

            // Achats et TVA déductible
            $table->decimal('purchases_domestic', 12, 2)->default(0)->comment('Achats Belgique - Grille 81');
            $table->decimal('vat_deductible', 12, 2)->default(0)->comment('TVA déductible - Grille 59');
            $table->decimal('purchases_intracommunity', 12, 2)->default(0)->comment('Achats UE - Grille 86');
            $table->decimal('vat_intracommunity', 12, 2)->default(0)->comment('TVA UE - Grille 88');

            // Totaux
            $table->decimal('vat_collected', 12, 2)->default(0);
            $table->decimal('vat_to_pay', 12, 2)->default(0);
            $table->decimal('vat_to_recover', 12, 2)->default(0);

            $table->enum('status', ['draft', 'validated', 'submitted', 'paid'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('payment_reference')->nullable()->comment('Communication structurée +++XXX/XXXX/XXXXX+++');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'year', 'month']);
            $table->index(['company_id', 'year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_declarations_be');
    }
};
