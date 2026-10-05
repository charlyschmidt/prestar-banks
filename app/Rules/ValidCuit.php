<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCuit implements ValidationRule
{
    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {

        $cuit = preg_replace('/\D/', '', (string) $value);

        if (strlen($cuit) !== 11) {
            $fail('Ingresá un CUIT válido.');
            return;
        }

        $multipliers = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];

        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $cuit[$i]) * $multipliers[$i];
        }

        $remainder = $sum % 11;

        $checkDigit = 11 - $remainder;

        if ($checkDigit === 11) {
            $checkDigit = 0;
        } elseif ($checkDigit === 10) {
            $checkDigit = 9;
        }

        if ($checkDigit !== (int) $cuit[10]) {
            $fail('El CUIT ingresado no es válido.');
        }
    }
}