<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class RemoveCartItem
{
    /**
     * @var string
     */
    private $cartId;

    /**
     * @var string
     */
    private $cartItemId;

    /**
     * @param string $cartId
     * @param string $cartItemId
     */
    private function __construct(string $cartId, string $cartItemId)
    {
        $this->cartId = $cartId;
        $this->cartItemId = $cartItemId;
    }

    /**
     * @param string $cartId
     * @param string $cartItemId
     *
     * @return RemoveCartItem
     */
    public static function create(string $cartId, string $cartItemId): self
    {
        return new self($cartId, $cartItemId);
    }

    /**
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getCartItemId(): string
    {
        return $this->cartItemId;
    }
}
