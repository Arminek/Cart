<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Money\Money;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\ValueObject\CartItemQuantity;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemQuantityIncreased
{
    /**
     * @var UuidInterface
     */
    private $cartItemId;

    /**
     * @var CartItemQuantity
     */
    private $newCartItemQuantity;

    /**
     * @var Money
     */
    private $newCartItemSubtotal;

    /**
     * @param UuidInterface $cartItemId
     * @param CartItemQuantity $newCartItemQuantity
     * @param Money $newCartItemSubtotal
     */
    private function __construct(
        UuidInterface $cartItemId,
        CartItemQuantity $newCartItemQuantity,
        Money $newCartItemSubtotal
    ) {
        $this->cartItemId = $cartItemId;
        $this->newCartItemQuantity = $newCartItemQuantity;
        $this->newCartItemSubtotal = $newCartItemSubtotal;
    }

    /**
     * @param UuidInterface $cartItemId
     * @param CartItemQuantity $newCartItemQuantity
     * @param Money $newCartItemSubtotal
     *
     * @return CartItemQuantityIncreased
     */
    public static function occur(
        UuidInterface $cartItemId,
        CartItemQuantity $newCartItemQuantity,
        Money $newCartItemSubtotal
    ): self {
        return new self($cartItemId, $newCartItemQuantity, $newCartItemSubtotal);
    }

    /**
     * @return UuidInterface
     */
    public function getCartItemId(): UuidInterface
    {
        return $this->cartItemId;
    }

    /**
     * @return CartItemQuantity
     */
    public function getNewCartItemQuantity(): CartItemQuantity
    {
        return $this->newCartItemQuantity;
    }

    /**
     * @return Money
     */
    public function getNewCartItemSubtotal(): Money
    {
        return $this->newCartItemSubtotal;
    }
}
