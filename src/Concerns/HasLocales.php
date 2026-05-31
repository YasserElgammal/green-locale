<?php

namespace YasserElgammal\GreenLocale\Concerns;

use YasserElgammal\GreenLocale\Events\LocaleValueForgotten;
use YasserElgammal\GreenLocale\Events\LocaleValueStored;
use YasserElgammal\GreenLocale\Events\LocaleValuesSynced;
use YasserElgammal\GreenLocale\Exceptions\LocaleAttributeNotDeclared;
use YasserElgammal\GreenLocale\Exceptions\MissingLocaleValueException;
use YasserElgammal\GreenLocale\LocaleManager;

trait HasLocales
{
    /** @var object[] */
    protected array $pendingLocaleEvents = [];

    public function putLocaleValue(string $attribute, string $locale, string $value): static
    {
        $this->guardLocaleAttribute($attribute);

        $data = $this->getLocaleData($attribute);
        $data[$locale] = $value;

        $this->setAttribute($attribute, json_encode($data, JSON_UNESCAPED_UNICODE));
        $this->pendingLocaleEvents[] = new LocaleValueStored($this, $attribute, $locale, $value);

        return $this;
    }

    public function localeValue(string $attribute, ?string $locale = null): ?string
    {
        $this->guardLocaleAttribute($attribute);

        $locale ??= $this->resolveCurrentLocale();
        $data = $this->getLocaleData($attribute);
        $value = $data[$locale] ?? $data[$this->resolveFallbackLocale()] ?? null;

        if ($value === null && LocaleManager::getInstance()->strict()) {
            throw MissingLocaleValueException::forAttribute($attribute, $locale);
        }

        return $value;
    }

    /** @return array<string, string> */
    public function localeValues(string $attribute): array
    {
        $this->guardLocaleAttribute($attribute);

        return $this->getLocaleData($attribute);
    }

    /** @param array<string, string> $values */
    public function syncLocaleValues(string $attribute, array $values): static
    {
        $this->guardLocaleAttribute($attribute);

        $this->setAttribute($attribute, json_encode($values, JSON_UNESCAPED_UNICODE));
        $this->pendingLocaleEvents[] = new LocaleValuesSynced($this, $attribute, $values);

        return $this;
    }

    public function forgetLocaleValue(string $attribute, string $locale): static
    {
        $this->guardLocaleAttribute($attribute);

        $data = $this->getLocaleData($attribute);
        unset($data[$locale]);

        $this->setAttribute($attribute, json_encode($data, JSON_UNESCAPED_UNICODE));
        $this->pendingLocaleEvents[] = new LocaleValueForgotten($this, $attribute, $locale);

        return $this;
    }

    public function hasLocaleValue(string $attribute, string $locale): bool
    {
        $this->guardLocaleAttribute($attribute);

        $data = $this->getLocaleData($attribute);

        return isset($data[$locale]) && $data[$locale] !== '';
    }

    /** @return object[] */
    public function localeEvents(): array
    {
        return $this->pendingLocaleEvents;
    }

    /** @return object[] */
    public function dispatchLocaleEvents(?callable $dispatcher = null): array
    {
        $events = $this->pendingLocaleEvents;
        $this->pendingLocaleEvents = [];

        if ($dispatcher !== null) {
            foreach ($events as $event) {
                $dispatcher($event);
            }
        }

        return $events;
    }

    /** @return array<string, string> */
    private function getLocaleData(string $attribute): array
    {
        $value = $this->getAttribute($attribute);

        if (is_array($value)) {
            return $this->normalizeLocaleData($value);
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $this->normalizeLocaleData($decoded) : [];
    }

    private function guardLocaleAttribute(string $attribute): void
    {
        $attributes = property_exists($this, 'localeAttributes') ? $this->localeAttributes : [];

        if (!in_array($attribute, $attributes, true)) {
            throw LocaleAttributeNotDeclared::forAttribute($attribute);
        }
    }

    private function resolveCurrentLocale(): string
    {
        return LocaleManager::getInstance()->current();
    }

    private function resolveFallbackLocale(): string
    {
        return LocaleManager::getInstance()->fallback();
    }

    /** @return array<string, string> */
    private function normalizeLocaleData(array $data): array
    {
        $normalized = [];
        foreach ($data as $locale => $value) {
            if (is_string($locale) && (is_string($value) || is_numeric($value))) {
                $normalized[$locale] = (string) $value;
            }
        }

        return $normalized;
    }
}
