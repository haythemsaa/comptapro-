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
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->string('invoice_number')->index();
            $table->string('supplier_invoice_number')->nullable(); // Numéro facture du fournisseur
            $table->enum('type', ['purchase', 'credit_note'])->default('purchase');
            $table->enum('status', ['draft', 'received', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->date('invoice_date')->index();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('payment_terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');

            // OCR fields - pour stocker les données extraites
            $table->text('ocr_data')->nullable(); // JSON avec les données OCR
            $table->string('original_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('ocr_processed')->default(false);
            $table->decimal('ocr_confidence', 5, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
