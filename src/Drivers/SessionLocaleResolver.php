<?php

namespace YasserElgammal\GreenLocale\Drivers;

use YasserElgammal\GreenLocale\Contracts\LocaleResolver;

class SessionLocaleResolver implements LocaleResolver
{
    public function __construct(private readonly string $key = 'locale')
    {
    }

    public function resolve(): ?string
    {
        if (function_exists('session')) {
            $value = session()->get($this->key);

            return is_string($value) && $value !== '' ? $value : null;
        }

        $value = $_SESSION[$this->key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
