<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AtLeastTwoWords implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $trim = trim((string) $value);
        if ($trim === '') {
            $fail(trans('validation.at_least_two_words'));

            return;
        }

        $parts = preg_split('/\s+/u', $trim, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false || count($parts) < 2) {
            $fail(trans('validation.at_least_two_words'));
        }
    }
}
