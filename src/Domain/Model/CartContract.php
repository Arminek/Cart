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
    public function addCartItem(string $productCode, int $quantity, int $price, string $productCurrencyCode): void;

    /**
     * @param string $cartItemId
     */
    public function removeCartItem(string $cartItemId): void;

    public function clear(): void;

    /**
     * @param string $cartItemId
     * @param int $quantity
     */
    public function changeCartItemQuantity(string $cartItemId, int $quantity): void;
}
