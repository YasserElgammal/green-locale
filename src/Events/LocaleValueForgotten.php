<?php

namespace YasserElgammal\GreenLocale\Events;

class LocaleValueForgotten
{
    public function __construct(
        public readonly object $model,
        public readonly string $attribute,
        public readonly string $locale,
    ) {
    }
}
