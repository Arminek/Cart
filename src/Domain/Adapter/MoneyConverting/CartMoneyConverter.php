<?php

namespace SyliusCart\Domain\Adapter\MoneyConverting;

use Money\Converter as BaseConverter;
use Money\Currency;
use Money\Money;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;
use SyliusCart\Domain\Adapter\Exchange\ExchangeRateProviderInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartMoneyConverter implements Converter
{
    /**
     * @var ExchangeRateProviderInterface
     */
    private $exchangeRateProvider;

    /**
     * @var AvailableCurrenciesProviderInterface
     */
    private $availableCurrenciesProvider;

    /**
     * @param ExchangeRateProviderInterface $exchangeRateProvider
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     */
    public function __construct(
        ExchangeRateProviderInterface $exchangeRateProvider,
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->availableCurrenciesProvider = $availableCurrenciesProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(Money $money, Currency $currency, int $roundingMode = Money::ROUND_UP): Money
    {
        $baseConverter = new BaseConverter(
            $this->availableCurrenciesProvider->provideAvailableCurrencies(),
            $this->exchangeRateProvider->provideExchange()
        );

        return $baseConverter->convert($money, $currency, $roundingMode);
    }
}
