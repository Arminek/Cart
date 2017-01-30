<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\EventSourcing\EventSourcedEntity;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Event\CartItemQuantityChanged;
use SyliusCart\Domain\Event\CartItemSubtotalChanged;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItem extends EventSourcedEntity
{
    /**
     * @var UuidInterface
     */
    private $cartItemId;

    /**
     * @var ProductCode
     */
    private $productCode;

    /**
     * @var CartItemQuantity
     */
    private $quantity;

    /**
     * @var Money
     */
    private $unitPrice;

    /**
     * @var Money
     */
    private $subtotal;

    /**
     * @param UuidInterface $cartItemId
     * @param ProductCode $productCode
     * @param CartItemQuantity $quantity
     * @param Money $unitPrice
     * @param Money $subtotal
     */
    public function __construct(
        UuidInterface $cartItemId,
        ProductCode $productCode,
        CartItemQuantity $quantity,
        Money $unitPrice,
        Money $subtotal
    ) {
        $this->cartItemId = $cartItemId;
        $this->productCode = $productCode;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subtotal = $subtotal;
    }

    /**
     * @param ProductCode $productCode
     * @param CartItemQuantity $quantity
     * @param Money $unitPrice
     *
     * @return CartItem
     */
    public static function create(ProductCode $productCode, CartItemQuantity $quantity, Money $unitPrice): self
    {
        $subtotal = $unitPrice->multiply($quantity->getNumber());

        return new self(Uuid::uuid4(), $productCode, $quantity, $unitPrice, $subtotal);
    }

    /**
     * @param CartItemQuantity $quantity
     */
    public function increaseQuantity(CartItemQuantity $quantity): void
    {
        $newQuantity = $this->quantity->add($quantity);
        $newSubtotal = $this->unitPrice->multiply($newQuantity->getNumber());

        $this->apply(CartItemQuantityChanged::occur($this->cartItemId, $newQuantity));
        $this->apply(CartItemSubtotalChanged::occur($this->cartItemId, $newSubtotal));
    }

    /**
     * @param CartItemQuantity $quantity
     */
    public function decreaseQuantity(CartItemQuantity $quantity): void
    {
        $newQuantity = $this->quantity->subtract($quantity);
        $newSubtotal = $this->unitPrice->multiply($newQuantity->getNumber());

        $this->apply(CartItemQuantityChanged::occur($this->cartItemId, $newQuantity));
        $this->apply(CartItemSubtotalChanged::occur($this->cartItemId, $newSubtotal));
    }

    /**
     * @param Money $newSubtotal
     */
    public function changeSubtotalBasedOnNewCurrency(Money $newSubtotal): void
    {
        $this->apply(CartItemSubtotalChanged::occur($this->cartItemId, $newSubtotal));
    }

    /**
     * @param CartItemQuantityChanged $event
     */
    protected function applyCartItemQuantityChanged(CartItemQuantityChanged $event): void
    {
        if ($this->cartItemId->equals($event->getCartItemId())) {
            $this->quantity = $event->getNewCartItemQuantity();
        }
    }

    /**
     * @param CartItemSubtotalChanged $event
     */
    protected function applyCartItemSubtotalChanged(CartItemSubtotalChanged $event): void
    {
        if ($this->cartItemId->equals($event->getCartItemId())) {
            $this->subtotal = $event->getNewSubtotal();
        }
    }

    /**
     * @return UuidInterface
     */
    public function cartItemId(): UuidInterface
    {
        return $this->cartItemId;
    }

    /**
     * @return ProductCode
     */
    public function productCode(): ProductCode
    {
        return $this->productCode;
    }

    /**
     * @return CartItemQuantity
     */
    public function quantity(): CartItemQuantity
    {
        return $this->quantity;
    }

    /**
     * @return Money
     */
    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    /**
     * @return Money
     */
    public function subtotal(): Money
    {
        return $this->subtotal;
    }
}
