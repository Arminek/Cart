<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartInitialized
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var Money
     */
    private $cartTotal;

    /**
     * @param UuidInterface $cartId
     * @param Money $cartTotal
     */
    private function __construct(UuidInterface $cartId, Money $cartTotal)
    {
        $this->cartId = $cartId;
        $this->cartTotal = $cartTotal;
    }

    /**
     * @param UuidInterface $cartId
     * @param Money $cartTotal
     *
     * @return self
     */
    public static function occur(UuidInterface $cartId, Money $cartTotal): self
    {
        return new self($cartId, $cartTotal);
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
    public function getCartTotal(): Money
    {
        return $this->cartTotal;
    }
}
