<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Service;

/**
 * @author Mateusz Zalewski <mateusz.zalewski@lakion.com>
 */
final class StringInflector
{
    /**
     * @param string $value
     *
     * @return string
     */
    public static function nameToCode(string $value): string
    {
        return str_replace([' ', '-'], '_', $value);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function nameToLowercaseCode(string $value): string
    {
        return strtolower(self::nameToCode($value));
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function nameToUppercaseCode(string $value): string
    {
        return strtoupper(self::nameToCode($value));
    }

    private function __construct()
    {
    }
}
