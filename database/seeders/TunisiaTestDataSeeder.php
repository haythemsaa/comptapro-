<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Payroll\Employee;
use App\Models\Payroll\PayrollSettings;
use App\Models\Banking\BankAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TunisiaTestDataSeeder extends Seeder
{
    /**
     * Seeder de données de test pour la Tunisie
     */
    public function run(): void
    {
        // Créer une entreprise de test tunisienne
        $company = $this->createTestCompany();

        // Créer les paramètres de paie
        $this->createPayrollSettings($company->id);

        // Créer des employés de test
        $this->createTestEmployees($company->id);

        // Créer des comptes bancaires
        $this->createBankAccounts($company->id);

        $this->command->info('✅ Données de test tunisiennes créées avec succès');
    }

    /**
     * Crée une entreprise de test
     */
    private function createTestCompany(): Company
    {
        $company = Company::create([
            'name' => 'Entreprise Test Tunisie SARL',
            'registration_number' => '1234567/ABC/A/M/000', // Matricule fiscal tunisien
            'vat_number' => '1234567/ABC/A/M/000',
            'address' => 'Avenue Habib Bourguiba',
            'city' => 'Tunis',
            'postal_code' => '1000',
            'country' => 'TN',
            'phone' => '+216 71 123 456',
            'email' => 'contact@test-tunisie.tn',
            'currency' => 'TND',
            'industry' => 'Services',
            'legal_structure' => 'SARL',
        ]);

        $this->command->info("✓ Entreprise créée: {$company->name}");

        return $company;
    }

    /**
     * Crée les paramètres de paie
     */
    private function createPayrollSettings(int $companyId): void
    {
        PayrollSettings::create([
            'company_id' => $companyId,
            'cnss_employee_rate' => 9.18,
            'cnss_employer_rate' => 16.57,
            'accident_insurance_rate' => 0.4,
            'cnss_ceiling' => 6000,
            'transport_tax_free' => 100,
            'irpp_brackets' => config('tunisia.irpp_brackets'),
            'working_days_per_month' => 26,
            'working_hours_per_day' => 8,
            'apply_transport_bonus' => true,
            'apply_seniority_bonus' => true,
        ]);

        $this->command->info('✓ Paramètres de paie créés');
    }

    /**
     * Crée des employés de test
     */
    private function createTestEmployees(int $companyId): void
    {
        $employees = [
            [
                'first_name' => 'Mohamed',
                'last_name' => 'Ben Ali',
                'cin' => '12345678',
                'cnss_number' => 'CNSS-001-2024',
                'birth_date' => '1990-05-15',
                'marital_status' => 'marié',
                'children_count' => 2,
                'email' => 'mohamed.benali@test.tn',
                'phone' => '+216 20 123 456',
                'address' => 'Rue de la République, Tunis',
                'hire_date' => '2020-01-15',
                'position' => 'Comptable',
                'department' => 'Finance',
                'contract_type' => 'CDI',
                'base_salary' => 1500,
            ],
            [
                'first_name' => 'Fatma',
                'last_name' => 'Trabelsi',
                'cin' => '87654321',
                'cnss_number' => 'CNSS-002-2024',
                'birth_date' => '1995-08-20',
                'marital_status' => 'célibataire',
                'children_count' => 0,
                'email' => 'fatma.trabelsi@test.tn',
                'phone' => '+216 22 234 567',
                'address' => 'Avenue Mohamed V, Sfax',
                'hire_date' => '2022-06-01',
                'position' => 'Développeuse',
                'department' => 'IT',
                'contract_type' => 'CDI',
                'base_salary' => 2000,
            ],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Gharbi',
                'cin' => '11223344',
                'cnss_number' => 'CNSS-003-2024',
                'birth_date' => '1988-03-10',
                'marital_status' => 'marié',
                'children_count' => 3,
                'email' => 'ahmed.gharbi@test.tn',
                'phone' => '+216 98 345 678',
                'address' => 'Rue Hedi Chaker, Sousse',
                'hire_date' => '2018-09-01',
                'position' => 'Directeur Commercial',
                'department' => 'Ventes',
                'contract_type' => 'CDI',
                'base_salary' => 3500,
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::create(array_merge(['company_id' => $companyId], $employeeData));
            $this->command->info("✓ Employé créé: {$employeeData['first_name']} {$employeeData['last_name']}");
        }
    }

    /**
     * Crée des comptes bancaires
     */
    private function createBankAccounts(int $companyId): void
    {
        $accounts = [
            [
                'account_name' => 'Compte Principal STB',
                'account_number' => '07 000 1234567890123',
                'iban' => 'TN59 07 000 1234567890123',
                'bic_swift' => 'STBKTNTT',
                'bank_name' => 'Société Tunisienne de Banque',
                'branch' => 'Agence Centrale Tunis',
                'currency' => 'TND',
                'account_code' => '5121',
                'current_balance' => 50000,
                'is_active' => true,
            ],
            [
                'account_name' => 'Compte Devises BIAT',
                'account_number' => '08 000 9876543210987',
                'iban' => 'TN59 08 000 9876543210987',
                'bic_swift' => 'BIATTNTT',
                'bank_name' => 'Banque Internationale Arabe de Tunisie',
                'branch' => 'Agence Lafayette',
                'currency' => 'EUR',
                'account_code' => '5122',
                'current_balance' => 10000,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $accountData) {
            BankAccount::create(array_merge(['company_id' => $companyId], $accountData));
            $this->command->info("✓ Compte bancaire créé: {$accountData['account_name']}");
        }
    }
}
