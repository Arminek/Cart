<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;
use SyliusCart\Domain\Event\CartCleared;
use SyliusCart\Domain\Event\CartInitialized;
use SyliusCart\Domain\Event\CartItemAdded;
use SyliusCart\Domain\Event\CartItemQuantityChanged;
use SyliusCart\Domain\Event\CartItemRemoved;
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
final class Cart extends EventSourcedAggregateRoot implements CartContract
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
     * @var Currency
     */
    private $cartCurrency;

    /**
     * @var AvailableCurrenciesProviderInterface
     */
    private $availableCurrenciesProvider;

    /**
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     */
    private function __construct(AvailableCurrenciesProviderInterface $availableCurrenciesProvider)
    {
        $this->availableCurrenciesProvider = $availableCurrenciesProvider;
    }

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
    ): CartContract {
        $cart = new self($availableCurrenciesProvider);

        $cartCurrency = new Currency($currencyCode);

        if (!$cartCurrency->isAvailableWithin($cart->availableCurrenciesProvider->provideAvailableCurrencies())) {
            throw new CartCurrencyNotSupportedException();
        }

        $cart->apply(CartInitialized::occur($cartId, $cartCurrency));

        return $cart;
    }

    /**
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     *
     * @return CartContract
     */
    public static function createWithAdapters(
        AvailableCurrenciesProviderInterface $availableCurrenciesProvider
    ): CartContract {
        return new self($availableCurrenciesProvider);
    }

    /**
     * @param string $productCode
     * @param int $quantity
     * @param int $price
     * @param string $productCurrencyCode
     */
    public function addProductToCart(string $productCode, int $quantity, int $price, string $productCurrencyCode): void
    {
        $price = new Money($price, new Currency($productCurrencyCode));
        $quantity = CartItemQuantity::create($quantity);
        $productCode = ProductCode::fromString($productCode);

        if ($price->isNegative()) {
            throw new InvalidCartItemUnitPriceException('Cart item unit price cannot be below zero.');
        }

        if (!$this->cartCurrency->equals($price->getCurrency())) {
            throw new CartCurrencyMismatchException($this->cartCurrency, $price->getCurrency());
        }

        $cartItem = CartItem::create(
            $productCode,
            $quantity,
            $price
        );

        $this->apply(CartItemAdded::occur($this->cartId, $cartItem));
    }

    /**
     * @param string $productCode
     */
    public function removeProductFromCart(string $productCode): void
    {
        $cartItem = $this->cartItems->findOneByProductCode(ProductCode::fromString($productCode));

        $this->apply(CartItemRemoved::occur($this->cartId, $cartItem->productCode()));
    }

    public function clear(): void
    {
        if (!$this->cartItems->isEmpty()) {
            $this->apply(CartCleared::occur($this->cartId));
        }
    }

    /**
     * @param string $productCode
     * @param int $quantity
     */
    public function changeProductQuantity(string $productCode, int $quantity): void
    {
        $productCode = ProductCode::fromString($productCode);
        $cartItem = $this->cartItems->findOneByProductCode($productCode);
        $newQuantity = CartItemQuantity::create($quantity);

        $this->apply(CartItemQuantityChanged::occur($this->cartId, $productCode, $cartItem->quantity(), $newQuantity));
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @param CartInitialized $event
     */
    protected function applyCartInitialized(CartInitialized $event): void
    {
        $this->cartId = $event->getCartId();
        $this->cartCurrency = $event->getCartCurrency();
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
        $cartItem = $this->cartItems->findOneByProductCode($event->getProductCode());
        $this->cartItems->remove($cartItem);
    }

    /**
     * @param CartCleared $event
     */
    protected function applyCartCleared(CartCleared $event): void
    {
        $this->cartItems->clear();
    }
}
