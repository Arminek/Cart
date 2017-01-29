<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Adapter\Exchange;

use Money\Exchange;
use Money\Exchange\FixedExchange;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class FixedCurrencyExchangeRateProvider implements ExchangeRateProviderInterface
{
    /**
     * @var Exchange
     */
    private $exchange;

    /**
     * @param array $fixedExchangeConfiguration
     */
    public function __construct(array $fixedExchangeConfiguration)
    {
        $this->exchange = new FixedExchange($fixedExchangeConfiguration);
    }

    /**
     * @return Exchange
     */
    public function provideExchange(): Exchange
    {
        return $this->exchange;
    }
}
