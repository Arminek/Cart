<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ChangeCartItemQuantity
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
     * @var int
     */
    private $quantity;

    /**
     * @param string $cartId
     * @param string $cartItemId
     * @param int $quantity
     */
    private function __construct(string $cartId, string $cartItemId, int $quantity)
    {
        $this->cartId = $cartId;
        $this->cartItemId = $cartItemId;
        $this->quantity = $quantity;
    }

    /**
     * @param string $cartId
     * @param string $cartItemId
     * @param int $quantity
     *
     * @return ChangeCartItemQuantity
     */
    public static function create(string $cartId, string $cartItemId, int $quantity): self
    {
        return new self($cartId, $cartItemId, $quantity);
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

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
