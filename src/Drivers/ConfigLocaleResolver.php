<?php

namespace YasserElgammal\GreenLocale\Drivers;

use YasserElgammal\GreenLocale\Contracts\LocaleResolver;

class ConfigLocaleResolver implements LocaleResolver
{
    public function __construct(private readonly string $locale)
    {
    }

    public function resolve(): ?string
    {
        return $this->locale;
    }
}
