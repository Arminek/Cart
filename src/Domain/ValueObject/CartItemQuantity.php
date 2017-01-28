<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\ValueObject;

use SyliusCart\Domain\Exception\InvalidCartItemQuantityException;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemQuantity
{
    /**
     * @var int
     */
    private $number;

    /**
     * @param int $number
     */
    private function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * @param int $number
     *
     * @return CartItemQuantity
     */
    public static function create(int $number): self
    {
        if (0 >= $number) {
            throw new InvalidCartItemQuantityException('Cart item quantity cannot be equals or below zero.');
        }

        return new self($number);
    }

    /**
     * @param CartItemQuantity $quantity
     *
     * @return CartItemQuantity
     */
    public function add(CartItemQuantity $quantity): self
    {
        $number = $this->number + $quantity->getNumber();

        return new self($number);
    }

    /**
     * @param CartItemQuantity $quantity
     *
     * @return bool
     */
    public function equals(CartItemQuantity $quantity): bool
    {
        return $quantity->getNumber() === $this->number;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }
}
