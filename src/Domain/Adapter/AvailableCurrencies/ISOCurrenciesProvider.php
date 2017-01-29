<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Adapter\AvailableCurrencies;

use Money\Currencies;
use Money\Currencies\ISOCurrencies;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ISOCurrenciesProvider implements AvailableCurrenciesProviderInterface
{
    /**
     * @var ISOCurrencies
     */
    private $isoCurrencies;

    public function __construct()
    {
        $this->isoCurrencies = new ISOCurrencies();
    }

    /**
     * {@inheritdoc}
     */
    public function provideAvailableCurrencies(): Currencies
    {
        return $this->isoCurrencies;
    }
}
