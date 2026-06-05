<?php

namespace Polinome\Trieur;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class FilterTypes
{
    public const string CONTAIN = 'contain';
    public const string DATE_RANGE = 'date_range';
    public const string EXACT = 'exact';

    private static array $types = [
        self::CONTAIN,
        self::DATE_RANGE,
        self::EXACT,
    ];

    public static function getTypes(): array
    {
        return self::$types;
    }

    public static function addType(string $type): void
    {
        if (!in_array($type, self::$types, true)) {
            self::$types[] = $type;
        }
    }
}
