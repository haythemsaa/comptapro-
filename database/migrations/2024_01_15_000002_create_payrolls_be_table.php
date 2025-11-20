<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls_be', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees_be')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('onss_employee', 10, 2)->comment('13.07%');
            $table->decimal('onss_employer', 10, 2)->comment('~27%');
            $table->decimal('withholding_tax', 10, 2)->comment('Précompte professionnel');
            $table->decimal('net_salary', 10, 2);
            $table->decimal('employer_cost', 10, 2);
            $table->decimal('holiday_pay', 10, 2)->default(0)->comment('Provision pécule');
            $table->decimal('meal_vouchers', 10, 2)->default(0);
            $table->decimal('eco_vouchers', 10, 2)->default(0);
            $table->decimal('transport_allowance', 10, 2)->default(0);
            $table->decimal('other_benefits', 10, 2)->default(0);
            $table->json('details')->nullable();
            $table->enum('status', ['draft', 'validated', 'paid'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'employee_id', 'year', 'month']);
            $table->index(['company_id', 'year', 'month']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls_be');
    }
};
