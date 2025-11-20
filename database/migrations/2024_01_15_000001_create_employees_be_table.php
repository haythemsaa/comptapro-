<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees_be', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('niss', 15)->unique()->comment('Numéro Sécurité Sociale');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'cohabiting'])->default('single');
            $table->integer('dependents')->default(0);
            $table->boolean('has_disability')->default(false);
            $table->decimal('gross_monthly_salary', 10, 2);
            $table->enum('contract_type', ['cdi', 'cdd', 'interim', 'student', 'freelance'])->default('cdi');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->string('bank_account_iban')->nullable();
            $table->string('bank_account_bic')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('niss');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees_be');
    }
};
