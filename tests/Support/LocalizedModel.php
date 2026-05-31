<?php

namespace YasserElgammal\GreenLocale\Tests\Support;

use YasserElgammal\GreenLocale\Concerns\HasLocales;

class LocalizedModel
{
    use HasLocales;

    protected array $attributes = [];
    protected array $localeAttributes = ['name', 'description'];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }
}
