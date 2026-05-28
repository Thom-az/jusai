<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida CNPJ calculando os dígitos verificadores.
 * Aceita CNPJ formatado (12.345.678/0001-90) ou só dígitos (12345678000190).
 */
class ValidCnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/\D/', '', (string) $value);

        if (strlen($cnpj) !== 14) {
            $fail('O CNPJ deve ter 14 dígitos.');
            return;
        }

        // Rejeita sequências repetidas (ex: 00000000000000)
        if (preg_match('/^(\d)\1+$/', $cnpj)) {
            $fail('O CNPJ informado é inválido.');
            return;
        }

        // Primeiro dígito verificador
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $firstDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $firstDigit) {
            $fail('O CNPJ informado é inválido.');
            return;
        }

        // Segundo dígito verificador
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $secondDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[13] !== $secondDigit) {
            $fail('O CNPJ informado é inválido.');
        }
    }
}
