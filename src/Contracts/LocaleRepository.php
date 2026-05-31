<?php

namespace YasserElgammal\GreenLocale\Contracts;

interface LocaleRepository
{
    public function current(): string;

    public function fallback(): string;

    /** @return string[] */
    public function available(): array;

    public function isAvailable(string $locale): bool;
}
