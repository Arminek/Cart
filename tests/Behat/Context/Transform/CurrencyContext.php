<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Money\Currency;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CurrencyContext implements Context
{
    /**
     * @Transform :currency
     */
    public function getTotalFromString(string $currency): Currency
    {
        return new Currency($currency);
    }
}
