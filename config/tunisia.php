<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration Tunisie - ComptaPro
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique pour le marché tunisien incluant les taux
    | fiscaux, les paramètres CNSS, et les intégrations gouvernementales.
    |
    */

    // Taux de TVA tunisiens
    'vat_rates' => [
        'standard' => 19,        // Taux normal
        'reduced' => 13,         // Taux réduit
        'super_reduced' => 7,    // Taux super-réduit
        'export' => 0,           // Exportations
    ],

    // Seuils de déclaration TVA
    'vat_declaration' => [
        'monthly_threshold' => 100000,    // CA > 100k TND = mensuel
        'due_day' => 28,                  // 28 du mois suivant
    ],

    // Taux CNSS 2024
    'cnss' => [
        'employee_rate' => 9.18,          // Part employé
        'employer_rate' => 16.57,         // Part employeur
        'accident_insurance_rate' => 0.4, // Assurance accidents (min)
        'ceiling' => 6000,                // Plafond mensuel en TND
        'due_day' => 15,                  // 15 du mois suivant
    ],

    // Barème IRPP 2024 (Article 44 du Code IRPP)
    'irpp_brackets' => [
        ['min' => 0, 'max' => 5000, 'rate' => 0],
        ['min' => 5000.001, 'max' => 20000, 'rate' => 26],
        ['min' => 20000.001, 'max' => 30000, 'rate' => 28],
        ['min' => 30000.001, 'max' => 50000, 'rate' => 32],
        ['min' => 50000.001, 'max' => PHP_FLOAT_MAX, 'rate' => 35],
    ],

    // Déductions familiales IRPP (Article 40 bis)
    'irpp_deductions' => [
        'married' => 150,        // Chef de famille (mensuel)
        'child_1' => 100,        // 1er enfant
        'child_2' => 90,         // 2ème enfant
        'child_3' => 80,         // 3ème enfant
        'child_4_plus' => 60,    // 4ème enfant et plus (chacun)
        'parent' => 150,         // Parents à charge (chacun)
    ],

    // Taux IS (Impôt sur les Sociétés) 2024
    'corporate_tax' => [
        'export_tech' => 15,     // Exportateurs et secteur tech
        'general' => 25,         // Taux général
        'special' => 35,         // Secteurs spéciaux
    ],

    // Acomptes provisionnels IS
    'corporate_tax_advances' => [
        'quarters' => [
            1 => ['month' => 6, 'day' => 30],   // Fin juin
            2 => ['month' => 9, 'day' => 30],   // Fin septembre
            3 => ['month' => 12, 'day' => 31],  // Fin décembre
        ],
        'final_due' => ['month' => 3, 'day' => 25], // 25 mars N+1
    ],

    // TFP (Taxe de Formation Professionnelle)
    'tfp' => [
        'rate_industry' => 2,    // 2% pour industrie
        'rate_other' => 1,       // 1% pour autres secteurs
        'due_day' => 28,         // Avec TVA
    ],

    // TCL (Taxe sur les établissements)
    'tcl' => [
        'rate' => 0.2,           // 0.2% sur salaires N-1
        'due_month' => 1,        // Janvier
        'due_day' => 31,
    ],

    // FOPROLOS
    'foprolos' => [
        'rate' => 1,             // 1% sur salaires
        'due_day' => 28,
    ],

    // Retenues à la source
    'withholding_tax' => [
        'honoraires' => 15,      // Honoraires
        'services' => 1.5,       // Services
        'rent' => 15,            // Loyers
        'commissions' => 15,     // Commissions
    ],

    // Prime d'ancienneté (selon législation tunisienne)
    'seniority_bonus' => [
        'start_years' => 2,      // Commence après 2 ans
        'initial_rate' => 5,     // 5% après 2 ans
        'increment_years' => 2,  // +2% tous les 2 ans
        'increment_rate' => 2,   // 2% d'augmentation
        'max_rate' => 30,        // Maximum 30%
    ],

    // Congés payés
    'leave' => [
        'annual_days' => 30,     // 30 jours/an (2.5 jours/mois)
        'sick_days_paid' => 6,   // 6 jours maladie payés
        'maternity_days' => 30,  // 30 jours congé maternité
        'paternity_days' => 1,   // 1 jour congé paternité
    ],

    // Jours ouvrés
    'working_days' => [
        'per_month' => 26,       // 26 jours/mois en moyenne
        'per_week' => 5,         // 5 jours/semaine
        'hours_per_day' => 8,    // 8h/jour
        'hours_per_week' => 40,  // 40h/semaine (max légal)
    ],

    // Heures supplémentaires
    'overtime' => [
        'rate_day' => 1.5,       // +50% heures sup jour
        'rate_night' => 1.75,    // +75% heures sup nuit
        'rate_weekend' => 1.75,  // +75% weekend
        'rate_holiday' => 2,     // +100% jours fériés
    ],

    // Jours fériés tunisiens 2024
    'public_holidays' => [
        '2024-01-01' => 'Nouvel An',
        '2024-01-14' => 'Fête de la Révolution',
        '2024-03-20' => 'Fête de l\'Indépendance',
        '2024-04-09' => 'Fête des Martyrs',
        '2024-04-10' => 'Aïd el-Fitr (estimé)',
        '2024-05-01' => 'Fête du Travail',
        '2024-06-16' => 'Aïd el-Adha (estimé)',
        '2024-07-07' => 'Nouvel An hégirien (estimé)',
        '2024-07-25' => 'Fête de la République',
        '2024-08-13' => 'Fête de la Femme',
        '2024-09-15' => 'Mawlid (estimé)',
        '2024-10-15' => 'Journée de l\'Évacuation',
    ],

    // El Fatoora (Facturation électronique)
    'elfatoora' => [
        'api_url' => env('ELFATOORA_API_URL', 'https://api.elfatoora.gov.tn'),
        'api_key' => env('ELFATOORA_API_KEY'),
        'certificate_path' => env('ELFATOORA_CERTIFICATE_PATH'),
        'private_key_path' => env('ELFATOORA_PRIVATE_KEY_PATH'),
        'private_key_password' => env('ELFATOORA_PRIVATE_KEY_PASSWORD'),
        'enabled' => env('ELFATOORA_ENABLED', false),
        'auto_transmit' => env('ELFATOORA_AUTO_TRANSMIT', false),
    ],

    // Format matricule fiscal tunisien
    'tax_id_format' => [
        'pattern' => '/^\d{7}\/[A-Z]{1,3}\/[A-Z]\/[A-Z]\/\d{3}$/',
        'example' => '1234567/ABC/A/M/000',
        'description' => '7 chiffres / Code / Type / Régime / Numéro',
    ],

    // Numérotation obligatoire
    'numbering' => [
        'invoice_prefix' => 'INV',
        'quote_prefix' => 'QUO',
        'credit_note_prefix' => 'CRN',
        'purchase_prefix' => 'ACH',
        'continuous_required' => true,  // Numérotation continue obligatoire
    ],

    // Pénalités de retard
    'late_penalties' => [
        'vat_monthly_rate' => 1.75,     // 1.75% par mois (0.5% pénalité + 1.25% intérêt)
        'tax_monthly_rate' => 1.75,
        'cnss_daily_rate' => 0.5,       // 0.5% par jour
    ],

    // Archivage légal
    'legal_archiving' => [
        'invoice_years' => 10,           // 10 ans pour factures
        'accounting_years' => 10,        // 10 ans pour comptabilité
        'payroll_years' => 5,            // 5 ans pour bulletins de paie
        'tax_years' => 10,               // 10 ans pour déclarations fiscales
    ],

    // Services IA
    'ai_services' => [
        'ocr' => [
            'provider' => env('OCR_PROVIDER', 'google'), // google, aws, azure, tesseract
            'google_api_key' => env('GOOGLE_CLOUD_VISION_API_KEY'),
            'aws_key' => env('AWS_ACCESS_KEY_ID'),
            'aws_secret' => env('AWS_SECRET_ACCESS_KEY'),
            'azure_key' => env('AZURE_COMPUTER_VISION_KEY'),
            'confidence_threshold' => 0.85,
        ],
        'assistant' => [
            'provider' => 'anthropic',
            'model' => 'claude-3-sonnet-20240229',
            'api_key' => env('ANTHROPIC_API_KEY'),
            'max_tokens' => 4096,
        ],
        'anomaly_detection' => [
            'enabled' => true,
            'auto_check' => true,
            'alert_critical' => true,
        ],
        'cash_flow_prediction' => [
            'enabled' => true,
            'default_days' => 90,
            'min_confidence' => 0.6,
        ],
    ],

    // Régimes spéciaux
    'special_regimes' => [
        'forfait' => [
            'name' => 'Régime forfaitaire',
            'max_revenue' => 100000,
        ],
        'reel_simplifie' => [
            'name' => 'Réel simplifié',
            'max_revenue' => 300000,
        ],
        'reel' => [
            'name' => 'Réel normal',
            'max_revenue' => PHP_FLOAT_MAX,
        ],
    ],

    // Banques tunisiennes (pour rapprochement bancaire)
    'banks' => [
        'STB' => 'Société Tunisienne de Banque',
        'BNA' => 'Banque Nationale Agricole',
        'BIAT' => 'Banque Internationale Arabe de Tunisie',
        'BT' => 'Banque de Tunisie',
        'ATB' => 'Arab Tunisian Bank',
        'UIB' => 'Union Internationale de Banques',
        'Amen Bank' => 'Amen Bank',
        'BH' => 'Banque de l\'Habitat',
        'BTE' => 'Banque Tuniso-Émiratie',
        'Attijari Bank' => 'Attijari Bank',
    ],

    // Support multilingue
    'locales' => [
        'fr' => 'Français',
        'ar' => 'العربية',
        'en' => 'English',
    ],

    'default_locale' => 'fr',
    'fallback_locale' => 'fr',
];
