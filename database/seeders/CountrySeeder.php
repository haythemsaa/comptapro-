<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'code' => 'BE',
                'name' => 'Belgique',
                'currency' => 'EUR',
                'default_vat_rate' => 21.00,
                'accounting_plan' => 'PCN',
                'vat_rates' => json_encode([21, 12, 6, 0]),
                'einvoicing_system' => 'Peppol B2B 2026',
                'vat_declaration_format' => 'Intervat XML',
                'is_active' => true,
            ],
            [
                'code' => 'FR',
                'name' => 'France',
                'currency' => 'EUR',
                'default_vat_rate' => 20.00,
                'accounting_plan' => 'PCG 2025',
                'vat_rates' => json_encode([20, 10, 5.5, 2.1]),
                'einvoicing_system' => 'Chorus Pro',
                'vat_declaration_format' => 'CA3/CA12',
                'is_active' => true,
            ],
            [
                'code' => 'CH',
                'name' => 'Suisse',
                'currency' => 'CHF',
                'default_vat_rate' => 8.10,
                'accounting_plan' => 'Plan KMU',
                'vat_rates' => json_encode([8.1, 2.6, 3.8]),
                'einvoicing_system' => 'Non obligatoire',
                'vat_declaration_format' => 'Portail AFC',
                'is_active' => true,
            ],
            [
                'code' => 'TN',
                'name' => 'Tunisie',
                'currency' => 'TND',
                'default_vat_rate' => 19.00,
                'accounting_plan' => 'Plan tunisien',
                'vat_rates' => json_encode([19, 13, 7, 0]),
                'einvoicing_system' => 'TTN / El Fatoora',
                'vat_declaration_format' => 'Format TEIF',
                'is_active' => true,
            ],
        ];

        foreach ($countries as $country) {
            \DB::table('countries')->insert($country);
        }
    }
}
