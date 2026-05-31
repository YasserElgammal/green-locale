<?php

namespace YasserElgammal\GreenLocale\Exceptions;

use RuntimeException;

class MissingLocaleValueException extends RuntimeException
{
    public static function forAttribute(string $attribute, string $locale): self
    {
        return new self(sprintf('Missing locale value for attribute [%s] and locale [%s].', $attribute, $locale));
    }
}
