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
        Schema::create('purchase_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null'); // Compte comptable
            $table->text('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit')->default('unité');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('vat_rate', 5, 2); // Taux de TVA
            $table->decimal('subtotal', 12, 2); // Sous-total HT
            $table->decimal('tax_amount', 12, 2); // Montant TVA
            $table->decimal('total', 12, 2); // Total TTC
            $table->integer('line_order')->default(0);
            $table->timestamps();

            $table->index(['purchase_invoice_id', 'line_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_lines');
    }
};
