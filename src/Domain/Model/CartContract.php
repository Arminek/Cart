<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\Domain\AggregateRoot;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface CartContract extends AggregateRoot
{
    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return CartContract
     */
    public static function initialize(
        UuidInterface $cartId,
        string $currencyCode,
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ): CartContract;

    /**
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return CartContract
     */
    public static function createWithAdapters(
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ): CartContract;

    /**
     * @param string $productCode
     * @param int $quantity
     * @param int $price
     *
     * @param string $productCurrencyCode
     */
    public function addProductToCart(string $productCode, int $quantity, int $price, string $productCurrencyCode): void;

    /**
     * @param string $productCode
     */
    public function removeProductFromCart(string $productCode): void;

    public function clear(): void;

    /**
     * @param string $productCode
     * @param int $quantity
     */
    public function changeProductQuantity(string $productCode, int $quantity): void;
}
