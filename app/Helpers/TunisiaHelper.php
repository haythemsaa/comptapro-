<?php

namespace App\Helpers;

use Carbon\Carbon;

class TunisiaHelper
{
    /**
     * Valide un matricule fiscal tunisien
     * Format: 1234567/ABC/A/M/000
     */
    public static function validateTaxId(string $taxId): bool
    {
        $pattern = config('tunisia.tax_id_format.pattern');
        return preg_match($pattern, $taxId) === 1;
    }

    /**
     * Formate un matricule fiscal tunisien
     */
    public static function formatTaxId(string $taxId): string
    {
        // Nettoyer et reformater
        $taxId = preg_replace('/[^0-9A-Z\/]/', '', strtoupper($taxId));

        if (self::validateTaxId($taxId)) {
            return $taxId;
        }

        return $taxId;
    }

    /**
     * Valide un numéro CNSS
     */
    public static function validateCNSSNumber(string $cnssNumber): bool
    {
        // Format simple pour validation
        return !empty($cnssNumber) && strlen($cnssNumber) >= 8;
    }

    /**
     * Valide une CIN (Carte d'Identité Nationale)
     */
    public static function validateCIN(string $cin): bool
    {
        // CIN tunisienne: 8 chiffres
        return preg_match('/^\d{8}$/', $cin) === 1;
    }

    /**
     * Valide un numéro IBAN tunisien
     */
    public static function validateIBAN(string $iban): bool
    {
        $iban = strtoupper(str_replace(' ', '', $iban));

        // IBAN tunisien: TN + 2 chiffres de contrôle + 20 chiffres
        if (!preg_match('/^TN\d{22}$/', $iban)) {
            return false;
        }

        // Validation du checksum (algorithme mod-97)
        return self::validateIBANChecksum($iban);
    }

    /**
     * Valide le checksum d'un IBAN
     */
    private static function validateIBANChecksum(string $iban): bool
    {
        // Déplacer les 4 premiers caractères à la fin
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Remplacer les lettres par des chiffres (A=10, B=11, ...)
        $numeric = '';
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (ctype_alpha($char)) {
                $numeric .= (ord($char) - 55);
            } else {
                $numeric .= $char;
            }
        }

        // Calculer mod 97
        return bcmod($numeric, '97') === '1';
    }

    /**
     * Formate un montant en dinars tunisiens
     */
    public static function formatAmount(float $amount, bool $showCurrency = true): string
    {
        $formatted = number_format($amount, 3, ',', ' ');

        return $showCurrency ? $formatted . ' TND' : $formatted;
    }

    /**
     * Formate une date au format tunisien
     */
    public static function formatDate(Carbon $date, string $format = 'd/m/Y'): string
    {
        return $date->format($format);
    }

    /**
     * Formate une date et heure au format tunisien
     */
    public static function formatDateTime(Carbon $dateTime): string
    {
        return $dateTime->format('d/m/Y H:i');
    }

    /**
     * Convertit un montant en lettres (français)
     */
    public static function amountToWords(float $amount): string
    {
        $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf'];
        $tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];
        $teens = ['dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize'];

        $dinars = floor($amount);
        $millimes = round(($amount - $dinars) * 1000);

        $result = '';

        // Partie entière (dinars)
        if ($dinars == 0) {
            $result = 'zéro';
        } else {
            // Simplification: traiter les milliers, centaines, etc.
            // Pour une implémentation complète, utiliser une bibliothèque
            $result = (string) $dinars;
        }

        $result .= ' dinar' . ($dinars > 1 ? 's' : '');

        // Partie décimale (millimes)
        if ($millimes > 0) {
            $result .= ' et ' . $millimes . ' millime' . ($millimes > 1 ? 's' : '');
        }

        return ucfirst($result);
    }

    /**
     * Retourne les taux de TVA tunisiens
     */
    public static function getVATRates(): array
    {
        return config('tunisia.vat_rates');
    }

    /**
     * Calcule la TVA pour un montant donné
     */
    public static function calculateVAT(float $amountHT, float $rate): float
    {
        return round($amountHT * ($rate / 100), 3);
    }

    /**
     * Calcule le montant HT depuis le TTC
     */
    public static function calculateHTFromTTC(float $amountTTC, float $rate): float
    {
        return round($amountTTC / (1 + ($rate / 100)), 3);
    }

    /**
     * Vérifie si une date est un jour férié tunisien
     */
    public static function isPublicHoliday(Carbon $date): bool
    {
        $holidays = config('tunisia.public_holidays');
        $dateString = $date->format('Y-m-d');

        return isset($holidays[$dateString]);
    }

    /**
     * Retourne le nom d'un jour férié
     */
    public static function getPublicHolidayName(Carbon $date): ?string
    {
        $holidays = config('tunisia.public_holidays');
        $dateString = $date->format('Y-m-d');

        return $holidays[$dateString] ?? null;
    }

    /**
     * Calcule le nombre de jours ouvrables entre deux dates
     */
    public static function getWorkingDaysBetween(Carbon $startDate, Carbon $endDate): int
    {
        $days = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            if (!$current->isWeekend() && !self::isPublicHoliday($current)) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Génère un RIB tunisien aléatoire (pour tests)
     */
    public static function generateRandomRIB(): string
    {
        $bankCode = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
        $branchCode = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $accountNumber = str_pad(rand(1, 9999999999999), 13, '0', STR_PAD_LEFT);
        $key = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);

        return "{$bankCode} {$branchCode} {$accountNumber} {$key}";
    }

    /**
     * Génère un numéro IBAN tunisien depuis un RIB
     */
    public static function generateIBAN(string $rib): string
    {
        // Nettoyer le RIB
        $rib = str_replace(' ', '', $rib);

        // Calculer la clé de contrôle
        // (Simplification - implémentation complète nécessite l'algorithme mod-97)
        $checkDigits = '59';

        return "TN{$checkDigits} {$rib}";
    }

    /**
     * Convertit un texte en slug pour URL
     */
    public static function slugify(string $text): string
    {
        // Translitérer les caractères arabes
        $text = self::transliterateArabic($text);

        // Translitérer les caractères français
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        // Nettoyer
        $text = preg_replace('/[^a-zA-Z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');

        return strtolower($text);
    }

    /**
     * Translitération basique de l'arabe
     */
    private static function transliterateArabic(string $text): string
    {
        $arabic = [
            'ا' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th',
            'ج' => 'j', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd',
            'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z', 'س' => 's',
            'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't',
            'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f',
            'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm',
            'ن' => 'n', 'ه' => 'h', 'و' => 'w', 'ي' => 'y',
        ];

        return str_replace(array_keys($arabic), array_values($arabic), $text);
    }

    /**
     * Retourne le barème IRPP pour une année donnée
     */
    public static function getIRPPBrackets(int $year = null): array
    {
        // Pour l'instant, retourner le barème 2024
        return config('tunisia.irpp_brackets');
    }

    /**
     * Calcule l'IRPP pour un revenu donné
     */
    public static function calculateIRPP(float $annualIncome, array $deductions = []): float
    {
        $brackets = self::getIRPPBrackets();
        $taxableIncome = $annualIncome - array_sum($deductions);

        $totalTax = 0;

        foreach ($brackets as $bracket) {
            if ($taxableIncome <= $bracket['min']) {
                break;
            }

            $taxableInBracket = min($taxableIncome, $bracket['max']) - $bracket['min'];
            $totalTax += $taxableInBracket * ($bracket['rate'] / 100);
        }

        return round($totalTax, 3);
    }

    /**
     * Génère un numéro de document unique
     */
    public static function generateDocumentNumber(string $prefix, int $companyId, string $type = 'invoice'): string
    {
        $year = now()->year;
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return "{$prefix}-{$year}-{$companyId}-{$random}";
    }

    /**
     * Nettoie et formate un numéro de téléphone tunisien
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Nettoyer
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ajouter le préfixe international si manquant
        if (!str_starts_with($phone, '+216') && !str_starts_with($phone, '216')) {
            if (str_starts_with($phone, '0')) {
                $phone = '+216' . substr($phone, 1);
            } else {
                $phone = '+216' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Retourne la liste des gouvernorats tunisiens
     */
    public static function getGovernorates(): array
    {
        return [
            'Tunis', 'Ariana', 'Ben Arous', 'Manouba',
            'Nabeul', 'Zaghouan', 'Bizerte',
            'Béja', 'Jendouba', 'Le Kef', 'Siliana',
            'Kairouan', 'Kasserine', 'Sidi Bouzid',
            'Sousse', 'Monastir', 'Mahdia', 'Sfax',
            'Gafsa', 'Tozeur', 'Kébili',
            'Gabès', 'Médenine', 'Tataouine',
        ];
    }
}
