<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Money\Currency;
use Money\Money;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class PriceContext implements Context
{
    /**
     * @Transform :price
     */
    public function getTotalFromString(string $price): Money
    {
        $currency = $this->getCurrency($price);
        $amount = $this->getAmount($price);

        return new Money($amount, $currency);
    }

    /**
     * @param string $priceText
     *
     * @return Currency
     *
     * @throws \RuntimeException
     */
    private function getCurrency(string $priceText): Currency
    {
        $currencySymbol = substr($priceText, 0, 1);
        $currencySymbolDictionary = ['$' => 'USD', '€' => 'EUR'];

        if (array_key_exists($currencySymbol, $currencySymbolDictionary)) {
            return new Currency($currencySymbolDictionary[$currencySymbol]);
        }

        throw new \RuntimeException(sprintf('This %s currency symbol is not supported', $currencySymbol));
    }

    /**
     * @param string $priceText
     *
     * @return int
     */
    private function getAmount(string $priceText): int
    {
        $amount = str_replace('.', '', substr($priceText, 1));

        return (int) $amount;
    }
}
