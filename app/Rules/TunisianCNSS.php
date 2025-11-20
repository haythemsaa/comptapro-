<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation du numéro CNSS tunisien
 * Format: 8 à 12 chiffres
 */
class TunisianCNSS implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer la valeur (enlever espaces et tirets)
        $value = preg_replace('/[\s\-]/', '', $value);

        // Vérifier que c'est uniquement des chiffres
        if (!ctype_digit($value)) {
            $fail('Le :attribute ne doit contenir que des chiffres');
            return;
        }

        // Vérifier la longueur (8 à 12 chiffres)
        $length = strlen($value);
        if ($length < 8 || $length > 12) {
            $fail('Le :attribute doit contenir entre 8 et 12 chiffres');
            return;
        }
    }
}
