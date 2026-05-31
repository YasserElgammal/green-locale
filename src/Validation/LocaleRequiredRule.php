<?php

namespace YasserElgammal\GreenLocale\Validation;

use Respect\Validation\Rules\AbstractRule;

class LocaleRequiredRule extends AbstractRule
{
    /** @param string[] $requiredLocales */
    public function __construct(private readonly array $requiredLocales)
    {
    }

    public function validate(mixed $input): bool
    {
        if (!is_array($input)) {
            return false;
        }

        foreach ($this->requiredLocales as $locale) {
            if (!isset($input[$locale]) || trim((string) $input[$locale]) === '') {
                return false;
            }
        }

        return true;
    }
}
