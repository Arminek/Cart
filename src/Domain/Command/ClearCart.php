<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ClearCart
{
    /**
     * @var string
     */
    private $cartId;

    /**
     * @param string $cartId
     */
    private function __construct(string $cartId)
    {
        $this->cartId = $cartId;
    }

    /**
     * @param string $cartId
     *
     * @return ClearCart
     */
    public static function create(string $cartId): self
    {
        return new self($cartId);
    }

    /**
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cartId;
    }
}
