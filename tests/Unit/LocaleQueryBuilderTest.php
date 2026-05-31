<?php

namespace YasserElgammal\GreenLocale\Tests\Unit;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;
use YasserElgammal\GreenLocale\LocaleQueryBuilder;

class LocaleQueryBuilderTest extends TestCase
{
    public function testAddsWhereLocaleClause(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $qb = $connection->createQueryBuilder()->select('*')->from('products');

        LocaleQueryBuilder::whereLocale($qb, 'name', 'en', 'Phone');

        $this->assertStringContainsString('JSON_EXTRACT(`name`, \'$."en"\')', $qb->getSQL());
        $this->assertSame('Phone', $qb->getParameter('locale_value'));
    }

    public function testAddsOrderByLocaleClause(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $qb = $connection->createQueryBuilder()->select('*')->from('products');

        LocaleQueryBuilder::orderByLocale($qb, 'name', 'ar', 'desc');

        $this->assertStringContainsString('ORDER BY JSON_UNQUOTE(JSON_EXTRACT(`name`, \'$."ar"\')) DESC', $qb->getSQL());
    }
}
