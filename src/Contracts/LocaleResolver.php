<?php

namespace YasserElgammal\GreenLocale\Contracts;

interface LocaleResolver
{
    public function resolve(): ?string;
}
