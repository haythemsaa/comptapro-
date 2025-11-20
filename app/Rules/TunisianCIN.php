<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation de la CIN tunisienne (Carte d'Identité Nationale)
 * Format: 8 chiffres
 */
class TunisianCIN implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer la valeur
        $value = preg_replace('/\s/', '', $value);

        // Vérifier le format: exactement 8 chiffres
        if (!preg_match('/^\d{8}$/', $value)) {
            $fail('Le :attribute doit être composé de 8 chiffres');
            return;
        }

        // Vérifier que le premier chiffre n'est pas 0
        if ($value[0] === '0') {
            $fail('Le :attribute ne peut pas commencer par 0');
            return;
        }
    }
}
