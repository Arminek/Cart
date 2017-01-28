<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartCleared
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @param UuidInterface $cartId
     */
    private function __construct(UuidInterface $cartId)
    {
        $this->cartId = $cartId;
    }

    /**
     * @param UuidInterface $cartId
     *
     * @return CartCleared
     */
    public static function occur(UuidInterface $cartId): self
    {
        return new self($cartId);
    }
}
