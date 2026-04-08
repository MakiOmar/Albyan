<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret_key');
        if ($secret === null || $secret === '') {
            return;
        }

        if (! is_string($value) || $value === '') {
            $fail(trans('validation.turnstile'));

            return;
        }

        try {
            $response = Http::timeout(10)->asForm()->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]
            );

            if ($response->json('success') !== true) {
                $fail(trans('validation.turnstile'));
            }
        } catch (\Throwable $e) {
            report($e);
            $fail(trans('validation.turnstile'));
        }
    }
}
