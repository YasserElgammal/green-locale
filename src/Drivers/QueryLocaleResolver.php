<?php

namespace YasserElgammal\GreenLocale\Drivers;

use YasserElgammal\GreenLocale\Contracts\LocaleResolver;

class QueryLocaleResolver implements LocaleResolver
{
    public function __construct(private readonly string $parameter = 'locale')
    {
    }

    public function resolve(): ?string
    {
        $value = $_GET[$this->parameter] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
