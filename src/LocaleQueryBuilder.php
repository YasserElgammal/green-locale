<?php

namespace YasserElgammal\GreenLocale;

use Doctrine\DBAL\Query\QueryBuilder;

class LocaleQueryBuilder
{
    public static function whereLocale(
        QueryBuilder $builder,
        string $column,
        string $locale,
        string $value,
        string $paramName = 'locale_value'
    ): QueryBuilder {
        return $builder
            ->andWhere(self::jsonExpression($builder, $column, $locale) . ' = :' . $paramName)
            ->setParameter($paramName, $value);
    }

    public static function whereLocaleLike(
        QueryBuilder $builder,
        string $column,
        string $locale,
        string $value,
        string $paramName = 'locale_value_like'
    ): QueryBuilder {
        return $builder
            ->andWhere(self::jsonExpression($builder, $column, $locale) . ' LIKE :' . $paramName)
            ->setParameter($paramName, $value);
    }

    public static function orderByLocale(
        QueryBuilder $builder,
        string $column,
        string $locale,
        string $direction = 'ASC'
    ): QueryBuilder {
        return $builder->orderBy(
            self::jsonExpression($builder, $column, $locale),
            strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC'
        );
    }

    private static function jsonExpression(QueryBuilder $builder, string $column, string $locale): string
    {
        $quotedColumn = self::quoteIdentifier($column);
        $path = '$."' . str_replace(['\\', '"'], ['\\\\', '\\"'], $locale) . '"';

        return "JSON_UNQUOTE(JSON_EXTRACT($quotedColumn, '$path'))";
    }

    private static function quoteIdentifier(string $identifier): string
    {
        if (str_contains($identifier, '(') || str_contains($identifier, '`')) {
            return $identifier;
        }

        return implode('.', array_map(
            fn (string $part): string => $part === '*' ? $part : '`' . $part . '`',
            explode('.', $identifier)
        ));
    }
}
