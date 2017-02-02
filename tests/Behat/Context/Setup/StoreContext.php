<?php

declare(strict_types = 1);

namespace Tests\SyliusCart\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Money\Currency;
use Money\Money;
use Tests\SyliusCart\Behat\Service\SharedStorageInterface;
use Tests\SyliusCart\Behat\Service\StringInflector;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class StoreContext implements Context
{
    /**
     * @var SharedStorageInterface
     */
    private $sharedStorage;

    /**
     * @var array
     */
    private $productCatalogue = [];

    /**
     * @var array
     */
    private $cartExchangeRateConfiguration = [];

    /**
     * @param SharedStorageInterface $sharedStorage
     */
    public function __construct(SharedStorageInterface $sharedStorage)
    {
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given the store has a product :productName priced at :price
     */
    public function theStoreHasAProductPricedAt(string $productName, Money $price): void
    {
        $productCode = StringInflector::nameToCode($productName);

        $this->productCatalogue[$productCode] = ['code' => $productCode, 'name' => $productName, 'price' => $price];
        $this->sharedStorage->set('product_catalogue', $this->productCatalogue);
    }

    /**
     * @Given the store operates in :currency currency
     */
    public function theStoreOperatesInCurrency(Currency $currency): void
    {
        $this->sharedStorage->set('currency', $currency);
    }

    /**
     * @Given the store has convert ratio :ratio between :baseCurrencyCode and :counterCurrencyCode
     */
    public function theStoreHasConvertRatioEqualsBetweenAnd(string $ratio, string $baseCurrencyCode, string $counterCurrencyCode): void
    {
        $this->cartExchangeRateConfiguration[$baseCurrencyCode] = [$counterCurrencyCode => $ratio];
        $this->sharedStorage->set('store_exchange_rate_configuration', $this->productCatalogue);
    }
}
