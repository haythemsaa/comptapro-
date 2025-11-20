<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation de l'IBAN tunisien
 * Format: TN + 2 chiffres de contrôle + 20 chiffres (24 caractères au total)
 */
class TunisianIBAN implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer la valeur (enlever les espaces)
        $value = strtoupper(str_replace(' ', '', $value));

        // Vérifier le format de base
        if (!preg_match('/^TN\d{22}$/', $value)) {
            $fail('Le :attribute doit être au format tunisien: TN + 22 chiffres (ex: TN5914207207100707129648)');
            return;
        }

        // Valider le checksum avec l'algorithme mod-97
        if (!$this->validateChecksum($value)) {
            $fail('Le :attribute a un checksum invalide');
            return;
        }
    }

    /**
     * Valide le checksum de l'IBAN selon l'algorithme mod-97
     */
    private function validateChecksum(string $iban): bool
    {
        // Déplacer les 4 premiers caractères à la fin
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        // Remplacer les lettres par des chiffres (A=10, B=11, ..., Z=35)
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
        // Pour les grands nombres, on utilise bcmod
        if (function_exists('bcmod')) {
            return bcmod($numeric, '97') === '1';
        }

        // Fallback sans bcmath (moins précis pour très grands nombres)
        $mod = 0;
        for ($i = 0; $i < strlen($numeric); $i++) {
            $mod = ($mod * 10 + intval($numeric[$i])) % 97;
        }

        return $mod === 1;
    }
}
