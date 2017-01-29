<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;
use SyliusCart\Domain\Adapter\MoneyConverting\Converter;
use SyliusCart\Domain\Event\CartCleared;
use SyliusCart\Domain\Event\CartCurrencyChanged;
use SyliusCart\Domain\Event\CartInitialized;
use SyliusCart\Domain\Event\CartItemAdded;
use SyliusCart\Domain\Event\CartItemQuantityIncreased;
use SyliusCart\Domain\Event\CartItemRemoved;
use SyliusCart\Domain\Event\CartRecalculated;
use SyliusCart\Domain\Exception\CartAlreadyEmptyException;
use SyliusCart\Domain\Exception\CartCurrencyMismatchException;
use SyliusCart\Domain\Exception\CartCurrencyNotSupportedException;
use SyliusCart\Domain\Exception\InvalidCartItemUnitPriceException;
use SyliusCart\Domain\ModelCollection\CartItemCollection;
use SyliusCart\Domain\ModelCollection\CartItems;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class Cart extends EventSourcedAggregateRoot
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var CartItemCollection
     */
    private $cartItems;

    /**
     * @var Money
     */
    private $total;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var AvailableCurrenciesProviderInterface
     */
    private $availableCurrenciesProvider;

    /**
     * @param Converter $converter
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     */
    private function __construct(Converter $converter, AvailableCurrenciesProviderInterface $availableCurrenciesProvider)
    {
        $this->converter = $converter;
        $this->availableCurrenciesProvider = $availableCurrenciesProvider;
    }

    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     * @param Converter $converter
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return Cart
     */
    public static function initialize(
        UuidInterface $cartId,
        string $currencyCode,
        Converter $converter,
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ): self {
        $cart = new self($converter, $availableCurrenciesProvider);

        $cartCurrency = new Currency($currencyCode);

        if (!$cartCurrency->isAvailableWithin($cart->availableCurrenciesProvider->provideAvailableCurrencies())) {
            throw new CartCurrencyNotSupportedException();
        }

        $cart->apply(CartInitialized::occur($cartId, new Money(0, $cartCurrency)));

        return $cart;
    }

    /**
     * @param Converter $converter
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return Cart
     */
    public static function createWithAdapters(
        Converter $converter,
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ): self {
        return new self($converter, $availableCurrenciesProvider);
    }

    /**
     * @param string $productCode
     * @param int $quantity
     * @param int $price
     * @param string $productCurrencyCode
     */
    public function addCartItem(string $productCode, int $quantity, int $price, string $productCurrencyCode): void
    {
        $price = new Money($price, new Currency($productCurrencyCode));
        $quantity = CartItemQuantity::create($quantity);
        $productCode = ProductCode::fromString($productCode);

        if ($price->isNegative()) {
            throw new InvalidCartItemUnitPriceException('Cart item unit price cannot be below zero.');
        }

        if (!$this->total->isSameCurrency($price)) {
            throw new CartCurrencyMismatchException($this->total->getCurrency(), $price->getCurrency());
        }

        if ($this->cartItems->existsWithProductCode($productCode)) {
            $cartItem = $this->cartItems->findOneByProductCode($productCode);
            $newTotal = $this->total->subtract($cartItem->subtotal());
            $cartItem->increaseQuantity($quantity);
            $newTotal = $newTotal->add($cartItem->subtotal());

            $this->apply(CartRecalculated::occur($this->cartId, $newTotal));

            return;
        }

        $this->addNewCartItem($productCode, $quantity, $price);
    }

    /**
     * @param string $cartItemId
     */
    public function removeCartItem(string $cartItemId): void
    {
        $cartItem = $this->cartItems->findOneById(Uuid::fromString($cartItemId));
        $newTotal = $this->total->subtract($cartItem->subtotal());

        $this->apply(CartItemRemoved::occur($this->cartId, $cartItem));
        $this->apply(CartRecalculated::occur($this->cartId, $newTotal));
    }

    public function clear(): void
    {
        if ($this->cartItems->isEmpty()) {
            throw new CartAlreadyEmptyException();
        }

        $newTotal = new Money(0, $this->total->getCurrency());

        $this->apply(CartCleared::occur($this->cartId));
        $this->apply(CartRecalculated::occur($this->cartId, $newTotal));
    }

    /**
     * @param string $currencyCode
     */
    public function changeCurrency(string $currencyCode): void
    {
        $oldCurrency = $this->total->getCurrency();
        $newCurrency = new Currency($currencyCode);

        if (!$newCurrency->isAvailableWithin($this->availableCurrenciesProvider->provideAvailableCurrencies())) {
            throw new CartCurrencyNotSupportedException();
        }

        $newTotal = $this->converter->convert($this->total, $newCurrency);

        /** @var CartItem $cartItem */
        foreach ($this->cartItems as $cartItem) {
            $newCartItemSubtotal = $this->converter->convert($cartItem->subtotal(), $newCurrency);
            $cartItem->changeSubtotalBasedOnNewCurrency($newCartItemSubtotal);
        }

        $this->apply(CartCurrencyChanged::occur($this->cartId, $newCurrency, $oldCurrency));
        $this->apply(CartRecalculated::occur($this->cartId, $newTotal));
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * {@inheritdoc}
     */
    protected function getChildEntities(): CartItemCollection
    {
        return $this->cartItems;
    }

    /**
     * @param CartInitialized $event
     */
    protected function applyCartInitialized(CartInitialized $event): void
    {
        $this->cartId = $event->getCartId();
        $this->total = $event->getCartTotal();
        $this->cartItems = CartItems::createEmpty();
    }

    /**
     * @param CartItemAdded $event
     */
    protected function applyCartItemAdded(CartItemAdded $event): void
    {
        $this->cartItems->add($event->getCartItem());
    }

    /**
     * @param CartItemRemoved $event
     */
    protected function applyCartItemRemoved(CartItemRemoved $event): void
    {
        $this->cartItems->remove($event->getCartItem());
    }

    /**
     * @param CartCleared $event
     */
    protected function applyCartCleared(CartCleared $event): void
    {
        $this->cartItems->clear();
    }

    /**
     * @param CartRecalculated $event
     */
    protected function applyCartRecalculated(CartRecalculated $event): void
    {
        $this->total = $event->getNewCartTotal();
    }

    /**
     * @param ProductCode $productCode
     * @param CartItemQuantity $quantity
     * @param Money $price
     */
    private function addNewCartItem(ProductCode $productCode, CartItemQuantity $quantity, Money $price): void
    {
        $cartItem = CartItem::create(
            $productCode,
            $quantity,
            $price
        );

        $newTotal = $this->total->add($cartItem->subtotal());
        $this->apply(CartItemAdded::occur($this->cartId, $cartItem));
        $this->apply(CartRecalculated::occur($this->cartId, $newTotal));
    }
}
