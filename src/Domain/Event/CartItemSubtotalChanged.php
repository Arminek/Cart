<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemSubtotalChanged
{
    /**
     * @var UuidInterface
     */
    private $cartItemId;

    /**
     * @var Money
     */
    private $newSubtotal;

    /**
     * @param UuidInterface $cartItemId
     * @param Money $newSubtotal
     */
    private function __construct(UuidInterface $cartItemId, Money $newSubtotal)
    {
        $this->cartItemId = $cartItemId;
        $this->newSubtotal = $newSubtotal;
    }

    /**
     * @param UuidInterface $cartItemId
     * @param Money $newSubtotal
     *
     * @return CartItemSubtotalChanged
     */
    public static function occur(UuidInterface $cartItemId, Money $newSubtotal): self
    {
        return new self($cartItemId, $newSubtotal);
    }

    /**
     * @return UuidInterface
     */
    public function getCartItemId(): UuidInterface
    {
        return $this->cartItemId;
    }

    /**
     * @return Money
     */
    public function getNewSubtotal(): Money
    {
        return $this->newSubtotal;
    }
}
