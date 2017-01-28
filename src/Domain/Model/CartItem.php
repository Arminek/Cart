<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\EventSourcing\EventSourcedEntity;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Event\CartItemQuantityIncreased;
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
     */
    private function __construct(UuidInterface $cartItemId, ProductCode $productCode, CartItemQuantity $quantity, Money $unitPrice)
    {
        $this->cartItemId = $cartItemId;
        $this->productCode = $productCode;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subtotal = $unitPrice->multiply($quantity->getNumber());
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
        $cartItem = new self(Uuid::uuid4(), $productCode, $quantity, $unitPrice);



        return $cartItem;
    }

    /**
     * @param CartItemQuantity $quantity
     */
    public function increaseQuantity(CartItemQuantity $quantity): void
    {
        $newQuantity = $this->quantity->add($quantity);
        $newSubtotal = $this->unitPrice->multiply($newQuantity->getNumber());

        $this->apply(CartItemQuantityIncreased::occur($this->cartItemId, $newQuantity, $newSubtotal));
    }

    /**
     * @param CartItemQuantityIncreased $event
     */
    protected function applyCartItemQuantityIncreased(CartItemQuantityIncreased $event): void
    {
        $this->quantity = $event->getNewCartItemQuantity();
        $this->subtotal = $event->getNewCartItemSubtotal();
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
