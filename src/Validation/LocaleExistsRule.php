<?php

namespace YasserElgammal\GreenLocale\Validation;

use Respect\Validation\Rules\AbstractRule;

class LocaleExistsRule extends AbstractRule
{
    /** @param string[] $availableLocales */
    public function __construct(private readonly array $availableLocales)
    {
    }

    public function validate(mixed $input): bool
    {
        if (!is_array($input)) {
            return false;
        }

        foreach (array_keys($input) as $locale) {
            if (!is_string($locale) || !in_array($locale, $this->availableLocales, true)) {
                return false;
            }
        }

        return true;
    }
}
