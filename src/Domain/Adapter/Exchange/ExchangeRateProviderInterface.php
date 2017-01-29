<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Adapter\Exchange;

use Money\Exchange;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface ExchangeRateProviderInterface
{
    /**
     * @return Exchange
     */
    public function provideExchange(): Exchange;
}
