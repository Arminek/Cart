<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Adapter\MoneyConverting;

use Money\Currency;
use Money\Money;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface Converter
{
    /**
     * @param Money $money
     * @param Currency $currency
     * @param int $roundingMode
     *
     * @return Money
     */
    public function convert(Money $money, Currency $currency, int $roundingMode = Money::ROUND_UP): Money;
}
