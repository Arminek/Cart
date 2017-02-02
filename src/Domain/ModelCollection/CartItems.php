<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\ModelCollection;

use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Exception\CartItemNotFoundException;
use SyliusCart\Domain\Model\CartItem;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItems implements CartItemCollection
{
    /**
     * @var \ArrayObject
     */
    private $items = [];

    /**
     * @param array $items
     */
    private function __construct(array $items = [])
    {
        $this->items = new \ArrayObject($items);
    }

    /**
     * @param array $items
     *
     * @return CartItems
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    /**
     * @return CartItems
     */
    public static function createEmpty(): self
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    public function add(CartItem $cartItem): void
    {
        $this->items->offsetSet((string) $cartItem->cartItemId(), $cartItem);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(CartItem $cartItem): void
    {
        if (!$this->exists($cartItem)) {
            throw new CartItemNotFoundException(sprintf('Cart item with product code "%s" does not exist.', $cartItem->productCode()));
        }

        $this->items->offsetUnset((string) $cartItem->cartItemId());
    }

    /**
     * {@inheritdoc}
     */
    public function findOneById(UuidInterface $cartItemId): CartItem
    {
        if (!$this->items->offsetExists((string) $cartItemId)) {
            throw new CartItemNotFoundException(sprintf('Cart item with id "%s" does not exist.', $cartItemId));
        }

        return $this->items->offsetGet((string) $cartItemId);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(CartItem $cartItem): bool
    {
        return $this->items->offsetExists((string) $cartItem->cartItemId());
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return $this->items->getArrayCopy();
    }

    public function clear(): void
    {
        $this->items = new \ArrayObject([]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \ArrayIterator
    {
        return $this->items->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return 0 === $this->items->count();
    }
}
