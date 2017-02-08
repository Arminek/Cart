<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Model;

use Broadway\EventSourcing\EventSourcedEntity;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItem
{
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
     * @param ProductCode $productCode
     * @param CartItemQuantity $quantity
     * @param Money $unitPrice
     */
    public function __construct(
        ProductCode $productCode,
        CartItemQuantity $quantity,
        Money $unitPrice
    ) {
        $this->productCode = $productCode;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
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
        return new self($productCode, $quantity, $unitPrice);
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
}
