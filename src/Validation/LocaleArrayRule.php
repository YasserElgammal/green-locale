<?php

namespace YasserElgammal\GreenLocale\Validation;

use Respect\Validation\Rules\AbstractRule;

class LocaleArrayRule extends AbstractRule
{
    public function validate(mixed $input): bool
    {
        if (!is_array($input)) {
            return false;
        }

        foreach (array_keys($input) as $key) {
            if (!is_string($key) || !preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/', $key)) {
                return false;
            }
        }

        return true;
    }
}
