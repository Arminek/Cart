<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Adapter\AvailableCurrencies;

use Money\Currencies;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface AvailableCurrenciesProviderInterface
{
    /**
     * @return Currencies
     */
    public function provideAvailableCurrencies(): Currencies;
}
