<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des employés
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('cin')->unique(); // Carte d'identité nationale
            $table->string('cnss_number')->unique(); // Numéro CNSS
            $table->string('cnrps_number')->nullable(); // Pour fonction publique
            $table->date('birth_date');
            $table->string('marital_status'); // célibataire, marié, divorcé, veuf
            $table->integer('children_count')->default(0);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('hire_date');
            $table->date('end_date')->nullable();
            $table->string('position');
            $table->string('department')->nullable();
            $table->enum('contract_type', ['CDI', 'CDD', 'CIVP', 'KARAMA', 'Stage'])->default('CDI');
            $table->decimal('base_salary', 12, 3); // En TND
            $table->boolean('is_active')->default(true);
            $table->json('bank_details')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        // Table des bulletins de paie
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('payslip_number')->unique();
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->date('payment_date');

            // Salaire de base et éléments
            $table->decimal('base_salary', 12, 3);
            $table->decimal('worked_days', 5, 2)->default(26);
            $table->decimal('worked_hours', 8, 2)->nullable();

            // Primes et indemnités (brutes)
            $table->decimal('transport_allowance', 12, 3)->default(0);
            $table->decimal('food_allowance', 12, 3)->default(0);
            $table->decimal('housing_allowance', 12, 3)->default(0);
            $table->decimal('seniority_bonus', 12, 3)->default(0);
            $table->decimal('performance_bonus', 12, 3)->default(0);
            $table->decimal('overtime_pay', 12, 3)->default(0);
            $table->decimal('other_bonuses', 12, 3)->default(0);

            // Totaux bruts
            $table->decimal('gross_salary', 12, 3); // Salaire brut total
            $table->decimal('taxable_gross', 12, 3); // Brut imposable (sans avantages non imposables)

            // Cotisations sociales employé
            $table->decimal('cnss_employee', 12, 3); // 9.18%
            $table->decimal('health_insurance_employee', 12, 3); // Mutuelle si applicable

            // Retenues
            $table->decimal('irpp_amount', 12, 3); // IRPP calculé
            $table->decimal('advance_payment', 12, 3)->default(0); // Avances
            $table->decimal('loan_deduction', 12, 3)->default(0); // Prêts
            $table->decimal('other_deductions', 12, 3)->default(0);
            $table->decimal('total_deductions', 12, 3);

            // Cotisations sociales employeur (pour charges patronales)
            $table->decimal('cnss_employer', 12, 3); // 16.57%
            $table->decimal('accident_insurance', 12, 3); // 0.4% à 4%
            $table->decimal('health_insurance_employer', 12, 3);

            // Salaire net
            $table->decimal('net_salary', 12, 3);
            $table->decimal('net_to_pay', 12, 3); // Net à payer après avances

            // Statut
            $table->enum('status', ['draft', 'validated', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->json('calculation_details')->nullable(); // Détails du calcul JSON

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['company_id', 'year', 'month']);
            $table->index('status');
        });

        // Table des déclarations CNSS mensuelles
        Schema::create('cnss_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('declaration_number')->unique();
            $table->integer('month');
            $table->integer('year');
            $table->integer('employee_count');

            // Totaux des cotisations
            $table->decimal('total_gross_salaries', 12, 3);
            $table->decimal('total_cnss_employee', 12, 3);
            $table->decimal('total_cnss_employer', 12, 3);
            $table->decimal('total_cnss', 12, 3); // Employé + Employeur
            $table->decimal('total_accident_insurance', 12, 3);
            $table->decimal('total_to_pay', 12, 3);

            // Dates limites
            $table->date('due_date'); // 15 du mois suivant
            $table->date('payment_date')->nullable();

            $table->enum('status', ['draft', 'submitted', 'paid', 'late'])->default('draft');
            $table->string('teledeclaration_reference')->nullable();
            $table->json('declaration_data')->nullable(); // Données complètes au format JSON
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['company_id', 'month', 'year']);
            $table->index('status');
        });

        // Table des congés
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('leave_type', [
                'annual', // Congé annuel
                'sick', // Congé maladie
                'maternity', // Congé maternité
                'paternity', // Congé paternité
                'unpaid', // Congé sans solde
                'exceptional' // Congé exceptionnel
            ]);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_count');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('attachments')->nullable(); // Certificats médicaux, etc.
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index('start_date');
        });

        // Table des soldes de congés
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('annual_entitlement', 5, 1)->default(30); // 30 jours/an en Tunisie
            $table->decimal('days_taken', 5, 1)->default(0);
            $table->decimal('days_remaining', 5, 1);
            $table->decimal('carried_forward', 5, 1)->default(0); // Report de l'année précédente
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });

        // Table des paramètres de paie
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Taux CNSS (mis à jour régulièrement)
            $table->decimal('cnss_employee_rate', 5, 2)->default(9.18);
            $table->decimal('cnss_employer_rate', 5, 2)->default(16.57);
            $table->decimal('accident_insurance_rate', 5, 2)->default(0.4);

            // Plafonds et limites
            $table->decimal('cnss_ceiling', 12, 3)->default(6000); // Plafond CNSS
            $table->decimal('transport_tax_free', 12, 3)->default(100); // Transport exonéré

            // Barème IRPP 2024 (stocké en JSON)
            $table->json('irpp_brackets')->nullable();

            // Paramètres entreprise
            $table->integer('working_days_per_month')->default(26);
            $table->decimal('working_hours_per_day', 5, 2)->default(8);
            $table->boolean('apply_transport_bonus')->default(true);
            $table->boolean('apply_seniority_bonus')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('cnss_declarations');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('employees');
    }
};
