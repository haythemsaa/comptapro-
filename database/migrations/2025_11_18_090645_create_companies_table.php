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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('registration_number')->nullable(); // Numéro d'entreprise
            $table->string('vat_number')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('logo_path')->nullable();
            $table->date('fiscal_year_start');
            $table->date('fiscal_year_end');
            $table->string('accounting_plan_type'); // standard, custom
            $table->enum('subscription_plan', ['starter', 'professional', 'enterprise'])->default('starter');
            $table->date('subscription_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
