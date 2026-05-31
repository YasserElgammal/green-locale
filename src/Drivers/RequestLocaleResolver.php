<?php

namespace YasserElgammal\GreenLocale\Drivers;

use YasserElgammal\GreenLocale\Contracts\LocaleResolver;

class RequestLocaleResolver implements LocaleResolver
{
    public function resolve(): ?string
    {
        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

        if (!is_string($header) || trim($header) === '') {
            return null;
        }

        $candidates = [];
        foreach (explode(',', $header) as $part) {
            [$locale, $quality] = array_pad(explode(';q=', trim($part), 2), 2, '1');
            $locale = str_replace('-', '_', trim($locale));

            if ($locale !== '') {
                $candidates[$locale] = (float) $quality;
            }
        }

        arsort($candidates);
        $locale = array_key_first($candidates);

        return is_string($locale) ? $locale : null;
    }
}
