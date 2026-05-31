<?php

namespace YasserElgammal\GreenLocale\Tests\Validation;

use PHPUnit\Framework\TestCase;
use YasserElgammal\GreenLocale\Validation\LocaleArrayRule;
use YasserElgammal\GreenLocale\Validation\LocaleExistsRule;
use YasserElgammal\GreenLocale\Validation\LocaleRequiredRule;

class LocaleValidationTest extends TestCase
{
    public function testLocaleArrayRuleValidatesLocaleKeys(): void
    {
        $rule = new LocaleArrayRule();

        $this->assertTrue($rule->validate(['en' => 'Phone', 'ar' => 'هاتف', 'en_US' => 'Phone']));
        $this->assertFalse($rule->validate(['english' => 'Phone']));
        $this->assertFalse($rule->validate('en'));
    }

    public function testLocaleRequiredRuleRequiresConfiguredLocales(): void
    {
        $rule = new LocaleRequiredRule(['en', 'ar']);

        $this->assertTrue($rule->validate(['en' => 'Phone', 'ar' => 'هاتف']));
        $this->assertFalse($rule->validate(['en' => 'Phone', 'ar' => '']));
    }

    public function testLocaleExistsRuleRejectsUnknownLocaleKeys(): void
    {
        $rule = new LocaleExistsRule(['en', 'ar']);

        $this->assertTrue($rule->validate(['en' => 'Phone']));
        $this->assertFalse($rule->validate(['fr' => 'Telephone']));
    }
}
