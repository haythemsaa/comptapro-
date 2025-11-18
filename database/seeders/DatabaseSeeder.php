<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Modules\Core\Models\{Country, Company};
use App\Models\Modules\Invoicing\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed countries first
        $this->call(CountrySeeder::class);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin ComptaPro',
            'email' => 'admin@comptapro.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create test user
        $user = User::create([
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Get Belgium country
        $belgium = Country::where('code', 'BE')->first();
        $france = Country::where('code', 'FR')->first();

        // Create Belgian company
        $companyBE = Company::create([
            'country_id' => $belgium->id,
            'name' => 'SPRL Comptabilité Pro',
            'legal_name' => 'Comptabilité Pro SPRL',
            'registration_number' => 'BE0123456789',
            'vat_number' => 'BE0123456789',
            'email' => 'contact@comptapro.be',
            'phone' => '+32 2 123 45 67',
            'address' => 'Rue de la Loi 42',
            'city' => 'Bruxelles',
            'postal_code' => '1000',
            'fiscal_year_start' => '2025-01-01',
            'fiscal_year_end' => '2025-12-31',
            'accounting_plan_type' => 'standard',
            'subscription_plan' => 'professional',
            'subscription_expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // Create French company
        $companyFR = Company::create([
            'country_id' => $france->id,
            'name' => 'SARL Compta Solutions',
            'legal_name' => 'Compta Solutions SARL',
            'registration_number' => 'FR123456789',
            'vat_number' => 'FR12345678901',
            'email' => 'contact@comptasolutions.fr',
            'phone' => '+33 1 23 45 67 89',
            'address' => '10 Avenue des Champs-Élysées',
            'city' => 'Paris',
            'postal_code' => '75008',
            'fiscal_year_start' => '2025-01-01',
            'fiscal_year_end' => '2025-12-31',
            'accounting_plan_type' => 'standard',
            'subscription_plan' => 'starter',
            'subscription_expires_at' => now()->addMonths(6),
            'is_active' => true,
        ]);

        // Attach users to companies
        $companyBE->users()->attach($admin->id, ['role' => 'admin', 'is_active' => true]);
        $companyBE->users()->attach($user->id, ['role' => 'accountant', 'is_active' => true]);
        $companyFR->users()->attach($admin->id, ['role' => 'admin', 'is_active' => true]);

        // Create sample customers for Belgian company
        Customer::create([
            'company_id' => $companyBE->id,
            'customer_number' => 'C001',
            'name' => 'Tech Solutions SA',
            'email' => 'contact@techsolutions.be',
            'phone' => '+32 2 234 56 78',
            'address' => 'Avenue Louise 123',
            'city' => 'Bruxelles',
            'postal_code' => '1050',
            'country_code' => 'BE',
            'vat_number' => 'BE0987654321',
            'payment_term' => '30_days',
            'is_active' => true,
        ]);

        Customer::create([
            'company_id' => $companyBE->id,
            'customer_number' => 'C002',
            'name' => 'Retail Plus SPRL',
            'email' => 'info@retailplus.be',
            'phone' => '+32 2 345 67 89',
            'address' => 'Rue Neuve 45',
            'city' => 'Bruxelles',
            'postal_code' => '1000',
            'country_code' => 'BE',
            'vat_number' => 'BE0123987654',
            'payment_term' => '15_days',
            'is_active' => true,
        ]);

        // Create sample customers for French company
        Customer::create([
            'company_id' => $companyFR->id,
            'customer_number' => 'C001',
            'name' => 'Entreprise Digitale SAS',
            'email' => 'contact@entreprise-digitale.fr',
            'phone' => '+33 1 34 56 78 90',
            'address' => '25 Rue de Rivoli',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country_code' => 'FR',
            'vat_number' => 'FR98765432101',
            'payment_term' => '30_days',
            'is_active' => true,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('---');
        $this->command->info('Admin Login: admin@comptapro.com / password');
        $this->command->info('User Login: jean@example.com / password');
    }
}
