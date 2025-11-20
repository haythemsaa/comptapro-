<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ComptaPro Belgium Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique pour le marché belge
    |
    */

    // Application
    'app_name' => env('APP_NAME_BE', 'ComptaPro Belgium'),
    'app_url' => env('APP_URL_BE', 'https://comptapro.be'),
    'timezone' => env('APP_TIMEZONE_BE', 'Europe/Brussels'),
    'locale' => env('APP_LOCALE_BE', 'fr'),
    'supported_locales' => explode(',', env('SUPPORTED_LOCALES_BE', 'fr,nl,en')),

    // TVA Belgique
    'vat' => [
        'rates' => [
            'normal' => (float) env('VAT_BE_RATE_NORMAL', 21),
            'intermediate' => (float) env('VAT_BE_RATE_INTERMEDIATE', 12),
            'reduced' => (float) env('VAT_BE_RATE_REDUCED', 6),
            'zero' => (float) env('VAT_BE_RATE_ZERO', 0),
        ],
        'period_threshold' => (int) env('VAT_BE_PERIOD_THRESHOLD', 2500000),
        'default_period' => env('VAT_BE_DEFAULT_PERIOD', 'monthly'),
        'deadline_day' => (int) env('VAT_BE_DEADLINE_DAY', 20),
    ],

    // ONSS (Sécurité Sociale)
    'onss' => [
        'employee_rate' => (float) env('ONSS_RATE_EMPLOYEE', 13.07) / 100,
        'employer_base_rate' => (float) env('ONSS_RATE_EMPLOYER_BASE', 25.00) / 100,
        'employer_average_rate' => (float) env('ONSS_RATE_EMPLOYER_AVERAGE', 27.00) / 100,
        'salary_ceiling' => (float) env('ONSS_SALARY_CEILING', null),
        'declaration_deadline_days' => (int) env('ONSS_DECLARATION_DEADLINE_DAYS', 31),
    ],

    // Précompte Professionnel
    'withholding_tax' => [
        'brackets' => [
            ['min' => 0, 'max' => 15200, 'rate' => 0.25],
            ['min' => 15200.01, 'max' => 26830, 'rate' => 0.40],
            ['min' => 26830.01, 'max' => 46440, 'rate' => 0.45],
            ['min' => 46440.01, 'max' => null, 'rate' => 0.50],
        ],
        'deduction_dependent' => (float) env('WITHHOLDING_TAX_DEDUCTION_DEPENDENT', 1750),
        'deduction_disability' => (float) env('WITHHOLDING_TAX_DEDUCTION_DISABILITY', 500),
        'married_reduction' => (float) env('WITHHOLDING_TAX_MARRIED_REDUCTION', 0.85),
    ],

    // Impôt des Sociétés
    'company_tax' => [
        'normal_rate' => (float) env('COMPANY_TAX_BE_RATE_NORMAL', 25) / 100,
        'sme_reduced_rate' => (float) env('COMPANY_TAX_BE_RATE_SME_REDUCED', 20) / 100,
        'sme_threshold' => (float) env('COMPANY_TAX_BE_SME_THRESHOLD', 100000),
        'sme_min_capital' => (float) env('COMPANY_TAX_BE_SME_MIN_CAPITAL', 61500),
        'sme_min_remuneration' => (float) env('COMPANY_TAX_BE_SME_MIN_REMUNERATION', 45000),
        'filing_months' => (int) env('COMPANY_TAX_FILING_MONTHS', 7),
        'notional_interest_rate' => (float) env('NOTIONAL_INTEREST_RATE', 3.2) / 100,
    ],

    // Pécule de Vacances
    'holiday_pay' => [
        'simple_rate' => (float) env('HOLIDAY_PAY_SIMPLE_RATE', 7.67) / 100,
        'double_rate' => (float) env('HOLIDAY_PAY_DOUBLE_RATE', 92) / 100,
    ],

    // PCMN (Plan Comptable Minimum Normalisé)
    'pcmn' => [
        'use_pcmn' => env('USE_PCMN', true),
        'multilingual' => env('PCMN_MULTILINGUAL_NAMES', true),
        'classes' => [
            '1' => 'Capitaux propres, provisions et dettes LT',
            '2' => 'Frais d\'établissement, actifs immobilisés',
            '3' => 'Stocks et commandes en cours',
            '4' => 'Créances et dettes à court terme',
            '5' => 'Placements de trésorerie',
            '6' => 'Charges',
            '7' => 'Produits',
            '0' => 'Droits et engagements hors bilan',
        ],
    ],

    // Banques belges
    'banks' => [
        'supported' => explode(',', env('SUPPORTED_BANKS_BE', 'BNP_Paribas_Fortis,KBC,Belfius,ING,Argenta,Crelan,AXA_Bank,BeoBank,Triodos')),
        'iban_country_code' => env('IBAN_COUNTRY_CODE', 'BE'),
        'iban_length' => (int) env('IBAN_LENGTH', 16),
    ],

    // BNB (Banque Nationale de Belgique) Reporting
    'bnb' => [
        'reporting_enabled' => env('BNB_REPORTING_ENABLED', true),
        'schema_version' => env('BNB_SCHEMA_VERSION', '2024'),
        'company_number' => env('BNB_COMPANY_NUMBER', null),
    ],

    // Automatisation
    'automation' => [
        'enabled' => env('AUTO_ACCOUNTING_ENABLED', true),
        'validation_enabled' => env('AUTO_VALIDATION_ENABLED', true),
        'validation_threshold' => (float) env('AUTO_VALIDATION_THRESHOLD', 0.90),
        'monthly_workflow_enabled' => env('AUTO_MONTHLY_WORKFLOW_ENABLED', true),
        'monthly_workflow_day' => (int) env('AUTO_MONTHLY_WORKFLOW_DAY', 25),
    ],

    // OCR
    'ocr' => [
        'provider' => env('OCR_PROVIDER', 'google'),
        'confidence_threshold' => (float) env('OCR_CONFIDENCE_THRESHOLD', 0.85),
    ],

    // Formats
    'formats' => [
        'date' => 'd/m/Y',
        'datetime' => 'd/m/Y H:i',
        'currency' => '€',
        'currency_position' => 'after', // before or after
        'decimal_separator' => ',',
        'thousands_separator' => '.',
    ],
];
