<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Model\CartItem;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemAdded
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var CartItem
     */
    private $cartItem;

    /**
     * @param UuidInterface $cartId
     * @param CartItem $cartItem
     */
    private function __construct(UuidInterface $cartId, CartItem $cartItem)
    {
        $this->cartId = $cartId;
        $this->cartItem = $cartItem;
    }

    /**
     * @param UuidInterface $cartId
     * @param CartItem $cartItem
     *
     * @return CartItemAdded
     */
    public static function occur(UuidInterface $cartId, CartItem $cartItem): self
    {
        return new self($cartId, $cartItem);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return CartItem
     */
    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }
}
