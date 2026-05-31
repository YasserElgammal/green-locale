<?php

namespace YasserElgammal\GreenLocale\Exceptions;

use InvalidArgumentException;

class InvalidLocaleException extends InvalidArgumentException
{
    /** @param string[] $availableLocales */
    public static function forLocale(string $locale, array $availableLocales): self
    {
        return new self(sprintf(
            'Locale [%s] is not available. Available locales: %s.',
            $locale,
            implode(', ', $availableLocales)
        ));
    }
}
