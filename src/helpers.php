<?php

use YasserElgammal\GreenLocale\LocaleManager;

if (!function_exists('locale_manager')) {
    function locale_manager(): LocaleManager
    {
        return LocaleManager::getInstance();
    }
}

if (!function_exists('current_locale')) {
    function current_locale(): string
    {
        return LocaleManager::getInstance()->current();
    }
}

if (!function_exists('fallback_locale')) {
    function fallback_locale(): string
    {
        return LocaleManager::getInstance()->fallback();
    }
}
