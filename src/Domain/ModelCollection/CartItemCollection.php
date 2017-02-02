<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\ModelCollection;

use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Exception\CartItemNotFoundException;
use SyliusCart\Domain\Model\CartItem;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface CartItemCollection extends \Countable, \IteratorAggregate
{
    /**
     * @param CartItem $cartItem
     */
    public function add(CartItem $cartItem): void;

    /**
     * @param CartItem $cartItem
     *
     * @throws CartItemNotFoundException
     */
    public function remove(CartItem $cartItem): void;

    /**
     * @param UuidInterface $cartItemId
     *
     * @return CartItem
     *
     * @throws CartItemNotFoundException
     */
    public function findOneById(UuidInterface $cartItemId): CartItem;

    /**
     * @param CartItem $cartItem
     *
     * @return bool
     */
    public function exists(CartItem $cartItem): bool;

    /**
     * @return array
     */
    public function findAll(): array;

    public function clear(): void;

    public function isEmpty(): bool;
}
