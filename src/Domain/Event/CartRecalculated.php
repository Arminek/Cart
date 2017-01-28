<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartRecalculated
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var Money
     */
    private $newCartTotal;

    /**
     * @param UuidInterface $cartId
     * @param Money $newCartTotal
     */
    private function __construct(UuidInterface $cartId, Money $newCartTotal)
    {
        $this->cartId = $cartId;
        $this->newCartTotal = $newCartTotal;
    }

    /**
     * @param UuidInterface $cartId
     * @param Money $newCartTotal
     *
     * @return CartRecalculated
     */
    public static function occur(UuidInterface $cartId, Money $newCartTotal): self
    {
        return new self($cartId, $newCartTotal);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return Money
     */
    public function getNewCartTotal(): Money
    {
        return $this->newCartTotal;
    }
}
