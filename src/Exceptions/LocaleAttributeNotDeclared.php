<?php

namespace YasserElgammal\GreenLocale\Exceptions;

use InvalidArgumentException;

class LocaleAttributeNotDeclared extends InvalidArgumentException
{
    public static function forAttribute(string $attribute): self
    {
        return new self(sprintf('Attribute [%s] is not declared as locale-aware.', $attribute));
    }
}
