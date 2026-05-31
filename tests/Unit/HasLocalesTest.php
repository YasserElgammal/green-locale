<?php

namespace YasserElgammal\GreenLocale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use YasserElgammal\GreenLocale\Events\LocaleValueForgotten;
use YasserElgammal\GreenLocale\Events\LocaleValueStored;
use YasserElgammal\GreenLocale\Events\LocaleValuesSynced;
use YasserElgammal\GreenLocale\Exceptions\LocaleAttributeNotDeclared;
use YasserElgammal\GreenLocale\LocaleManager;
use YasserElgammal\GreenLocale\Tests\Support\LocalizedModel;

class HasLocalesTest extends TestCase
{
    protected function setUp(): void
    {
        LocaleManager::setInstance(new LocaleManager([
            'default_locale' => 'ar',
            'fallback_locale' => 'en',
            'available_locales' => ['en', 'ar'],
        ]));
    }

    protected function tearDown(): void
    {
        LocaleManager::setInstance(null);

        parent::tearDown();
    }

    public function testStoresAndReadsLocaleValues(): void
    {
        $model = new LocalizedModel();

        $model->putLocaleValue('name', 'en', 'Phone')
            ->putLocaleValue('name', 'ar', 'هاتف');

        $this->assertSame('هاتف', $model->localeValue('name', 'ar'));
        $this->assertSame(['en' => 'Phone', 'ar' => 'هاتف'], $model->localeValues('name'));
        $this->assertInstanceOf(LocaleValueStored::class, $model->localeEvents()[0]);
    }

    public function testFallsBackWhenCurrentLocaleIsMissing(): void
    {
        $model = new LocalizedModel([
            'name' => json_encode(['en' => 'Phone'], JSON_UNESCAPED_UNICODE),
        ]);

        $this->assertSame('Phone', $model->localeValue('name'));
    }

    public function testSyncReplacesAllValues(): void
    {
        $model = new LocalizedModel([
            'name' => json_encode(['en' => 'Phone', 'ar' => 'هاتف'], JSON_UNESCAPED_UNICODE),
        ]);

        $model->syncLocaleValues('name', ['ar' => 'هاتف فقط']);

        $this->assertSame(['ar' => 'هاتف فقط'], $model->localeValues('name'));
        $this->assertInstanceOf(LocaleValuesSynced::class, $model->localeEvents()[0]);
    }

    public function testForgetsLocaleValue(): void
    {
        $model = new LocalizedModel([
            'name' => json_encode(['en' => 'Phone', 'ar' => 'هاتف'], JSON_UNESCAPED_UNICODE),
        ]);

        $model->forgetLocaleValue('name', 'ar');

        $this->assertFalse($model->hasLocaleValue('name', 'ar'));
        $this->assertSame(['en' => 'Phone'], $model->localeValues('name'));
        $this->assertInstanceOf(LocaleValueForgotten::class, $model->localeEvents()[0]);
    }

    public function testRejectsUndeclaredAttribute(): void
    {
        $model = new LocalizedModel();

        $this->expectException(LocaleAttributeNotDeclared::class);

        $model->localeValue('title');
    }
}
