<?php

namespace YasserElgammal\GreenLocale\Events;

class LocaleValuesSynced
{
    /** @param array<string, string> $values */
    public function __construct(
        public readonly object $model,
        public readonly string $attribute,
        public readonly array $values,
    ) {
    }
}
