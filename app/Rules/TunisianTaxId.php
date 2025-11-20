<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation du matricule fiscal tunisien
 * Format: 1234567/ABC/A/M/000
 */
class TunisianTaxId implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer la valeur
        $value = strtoupper(trim($value));

        // Vérifier le format avec regex
        $pattern = '/^\d{7}\/[A-Z]{1,3}\/[A-Z]\/[A-Z]\/\d{3}$/';

        if (!preg_match($pattern, $value)) {
            $fail('Le :attribute doit être au format tunisien: 1234567/ABC/A/M/000');
            return;
        }

        // Extraire les composants
        $parts = explode('/', $value);

        // Vérifier que le numéro de base est bien composé de 7 chiffres
        if (!ctype_digit($parts[0]) || strlen($parts[0]) !== 7) {
            $fail('Le :attribute doit commencer par 7 chiffres');
            return;
        }

        // Vérifier le code catégorie (1 à 3 lettres)
        if (!ctype_alpha($parts[1]) || strlen($parts[1]) > 3) {
            $fail('Le code catégorie du :attribute est invalide');
            return;
        }

        // Vérifier le code établissement (1 lettre)
        if (!ctype_alpha($parts[2]) || strlen($parts[2]) !== 1) {
            $fail('Le code établissement du :attribute est invalide');
            return;
        }

        // Vérifier le code nature (1 lettre)
        if (!ctype_alpha($parts[3]) || strlen($parts[3]) !== 1) {
            $fail('Le code nature du :attribute est invalide');
            return;
        }

        // Vérifier la clé (3 chiffres)
        if (!ctype_digit($parts[4]) || strlen($parts[4]) !== 3) {
            $fail('La clé du :attribute doit être composée de 3 chiffres');
            return;
        }
    }
}
