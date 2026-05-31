<?php

namespace YasserElgammal\GreenLocale;

use YasserElgammal\GreenLocale\Contracts\LocaleRepository;
use YasserElgammal\GreenLocale\Contracts\LocaleResolver;
use YasserElgammal\GreenLocale\Exceptions\InvalidLocaleException;

class LocaleManager implements LocaleRepository
{
    private static ?self $instance = null;

    private string $currentLocale;
    private string $fallbackLocale;

    /** @var string[] */
    private array $availableLocales;

    /** @var LocaleResolver[] */
    private array $resolvers = [];

    private ?string $resolvedLocale = null;
    private bool $strict;

    public function __construct(array $config = [])
    {
        $this->availableLocales = array_values($config['available_locales'] ?? ['en']);
        $this->currentLocale = (string) ($config['default_locale'] ?? $this->availableLocales[0] ?? 'en');
        $this->fallbackLocale = (string) ($config['fallback_locale'] ?? $this->currentLocale);
        $this->strict = (bool) ($config['strict'] ?? false);

        $this->ensureConfiguredLocale($this->currentLocale);
        $this->ensureConfiguredLocale($this->fallbackLocale);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function setInstance(?self $instance): void
    {
        self::$instance = $instance;
    }

    public function current(): string
    {
        return $this->resolvedLocale ?? $this->resolveLocale();
    }

    public function fallback(): string
    {
        return $this->fallbackLocale;
    }

    public function available(): array
    {
        return $this->availableLocales;
    }

    public function isAvailable(string $locale): bool
    {
        return in_array($locale, $this->availableLocales, true);
    }

    public function strict(): bool
    {
        return $this->strict;
    }

    public function setLocale(string $locale): void
    {
        $this->guardAvailableLocale($locale);
        $this->currentLocale = $locale;
        $this->resolvedLocale = $locale;
    }

    public function setFallbackLocale(string $locale): void
    {
        $this->guardAvailableLocale($locale);
        $this->fallbackLocale = $locale;
    }

    public function addResolver(LocaleResolver $resolver): void
    {
        $this->resolvers[] = $resolver;
        $this->resolvedLocale = null;
    }

    public function resolveLocale(): string
    {
        foreach ($this->resolvers as $resolver) {
            $locale = $resolver->resolve();

            if ($locale !== null && $this->isAvailable($locale)) {
                return $this->resolvedLocale = $locale;
            }
        }

        return $this->resolvedLocale = $this->currentLocale;
    }

    private function guardAvailableLocale(string $locale): void
    {
        if (!$this->isAvailable($locale)) {
            throw InvalidLocaleException::forLocale($locale, $this->availableLocales);
        }
    }

    private function ensureConfiguredLocale(string $locale): void
    {
        if (!$this->isAvailable($locale)) {
            $this->availableLocales[] = $locale;
        }
    }
}
