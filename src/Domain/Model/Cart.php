<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Event\CartInitialized;
use SyliusCart\Domain\Event\CartItemAdded;
use SyliusCart\Domain\Event\CartItemQuantityIncreased;
use SyliusCart\Domain\Event\CartRecalculated;
use SyliusCart\Domain\Exception\CartCurrencyMismatchException;
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
     * @param UuidInterface $cartId
     * @param string $currencyCode
     *
     * @return Cart
     */
    public static function initialize(UuidInterface $cartId, string $currencyCode): self
    {
        $cart = new self();

        $cart->apply(CartInitialized::occur($cartId, new Money(0, new Currency($currencyCode))));

        return $cart;
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

        $cartItem = CartItem::create(
            $productCode,
            $quantity,
            $price
        );

        if ($this->cartItems->existsWithProductCode($cartItem->productCode())) {
            $cartItem = $this->cartItems->findOneByProductCode($cartItem->productCode());
            $cartItem->increaseQuantity($quantity);

            return;
        }

        $this->addNewCartItem($cartItem);
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
        $this->recalculateTotal();
    }

    /**
     * @param CartItemQuantityIncreased $event
     */
    protected function applyCartItemQuantityIncreased(CartItemQuantityIncreased $event): void
    {
        $this->recalculateTotalDuringCartItemQuantityIncreasing($event);
    }

    /**
     * @param CartRecalculated $event
     */
    protected function applyCartRecalculated(CartRecalculated $event): void
    {
        $this->total = $event->getNewCartTotal();
    }

    /**
     * @param CartItem $cartItem
     */
    private function addNewCartItem(CartItem $cartItem): void
    {
        $this->apply(CartItemAdded::occur($this->cartId, $cartItem));
    }

    private function recalculateTotal(): void
    {
        $newTotal = new Money(0, $this->total->getCurrency());

        /** @var CartItem $cartItem */
        foreach ($this->cartItems as $cartItem) {
            $newTotal = $newTotal->add($cartItem->subtotal());
        }

        $this->apply(CartRecalculated::occur($this->cartId, $newTotal));
    }

    /**
     * @param CartItemQuantityIncreased $event
     */
    private function recalculateTotalDuringCartItemQuantityIncreasing(CartItemQuantityIncreased $event): void
    {
        $newTotal = new Money(0, $this->total->getCurrency());

        /** @var CartItem $cartItem */
        foreach ($this->cartItems as $cartItem) {
            if ($cartItem->cartItemId()->equals($event->getCartItemId())) {
                $newTotal = $newTotal->add($event->getNewCartItemSubtotal());

                continue;
            }

            $newTotal = $newTotal->add($cartItem->subtotal());
        }

        $this->apply(CartRecalculated::occur($this->cartId, $newTotal));
    }
}
