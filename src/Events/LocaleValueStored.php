<?php

namespace YasserElgammal\GreenLocale\Events;

class LocaleValueStored
{
    public function __construct(
        public readonly object $model,
        public readonly string $attribute,
        public readonly string $locale,
        public readonly string $value,
    ) {
    }
}
