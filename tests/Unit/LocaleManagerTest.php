<?php

namespace YasserElgammal\GreenLocale\Tests\Unit;

use PHPUnit\Framework\TestCase;
use YasserElgammal\GreenLocale\Contracts\LocaleResolver;
use YasserElgammal\GreenLocale\Exceptions\InvalidLocaleException;
use YasserElgammal\GreenLocale\LocaleManager;

class LocaleManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        LocaleManager::setInstance(null);

        parent::tearDown();
    }

    public function testReadsConfiguredLocaleState(): void
    {
        $manager = new LocaleManager([
            'default_locale' => 'ar',
            'fallback_locale' => 'en',
            'available_locales' => ['en', 'ar'],
        ]);

        $this->assertSame('ar', $manager->current());
        $this->assertSame('en', $manager->fallback());
        $this->assertSame(['en', 'ar'], $manager->available());
        $this->assertTrue($manager->isAvailable('ar'));
    }

    public function testResolverChainUsesFirstAvailableLocale(): void
    {
        $manager = new LocaleManager([
            'default_locale' => 'en',
            'fallback_locale' => 'en',
            'available_locales' => ['en', 'ar'],
        ]);

        $manager->addResolver(new class implements LocaleResolver {
            public function resolve(): ?string
            {
                return null;
            }
        });
        $manager->addResolver(new class implements LocaleResolver {
            public function resolve(): ?string
            {
                return 'ar';
            }
        });

        $this->assertSame('ar', $manager->current());
    }

    public function testExplicitSetLocaleRejectsUnknownLocale(): void
    {
        $manager = new LocaleManager([
            'available_locales' => ['en'],
        ]);

        $this->expectException(InvalidLocaleException::class);

        $manager->setLocale('fr');
    }
}
