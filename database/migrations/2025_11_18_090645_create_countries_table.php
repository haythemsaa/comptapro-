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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique(); // BE, FR, CH, TN
            $table->string('name');
            $table->string('currency', 3); // EUR, CHF, TND
            $table->decimal('default_vat_rate', 5, 2); // 21.00, 20.00, 8.10, 19.00
            $table->string('accounting_plan'); // PCN, PCG, KMU, Tunisian
            $table->json('vat_rates')->nullable(); // [21, 12, 6, 0] pour BE
            $table->string('einvoicing_system')->nullable(); // Peppol, Chorus Pro, TTN
            $table->string('vat_declaration_format')->nullable(); // Intervat XML, CA3/CA12, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
