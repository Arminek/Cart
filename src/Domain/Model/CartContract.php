<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\Domain\AggregateRoot;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;
use SyliusCart\Domain\Adapter\MoneyConverting\Converter;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface CartContract extends AggregateRoot
{
    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     * @param Converter $converter
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return CartContract
     */
    public static function initialize(
        UuidInterface $cartId,
        string $currencyCode,
        Converter $converter,
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ): CartContract;

    /**
     * @param Converter $converter
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return CartContract
     */
    public static function createWithAdapters(
        Converter $converter,
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
     * @param string $currencyCode
     */
    public function changeCurrency(string $currencyCode): void;

    /**
     * @param string $cartItemId
     * @param int $quantity
     */
    public function changeCartItemQuantity(string $cartItemId, int $quantity): void;
}
