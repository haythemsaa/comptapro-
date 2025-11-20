<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation du taux de TVA tunisien
 * Taux autorisés: 0%, 7%, 13%, 19%
 */
class TunisianVATRate implements ValidationRule
{
    private array $allowedRates = [0, 7, 13, 19];

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Convertir en nombre
        $value = floatval($value);

        // Vérifier que c'est un taux autorisé
        if (!in_array($value, $this->allowedRates, true)) {
            $fail('Le :attribute doit être l\'un des taux tunisiens autorisés: 0%, 7%, 13%, ou 19%');
            return;
        }
    }
}
