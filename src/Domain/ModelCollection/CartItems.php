<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\ModelCollection;

use SyliusCart\Domain\Exception\CartItemNotFoundException;
use SyliusCart\Domain\Model\CartItem;
use SyliusCart\Domain\ValueObject\ProductCode;

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
        $this->items->offsetSet((string) $cartItem->productCode(), $cartItem);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(CartItem $cartItem): void
    {
        if (!$this->exists($cartItem)) {
            throw new CartItemNotFoundException(sprintf('Cart item with product code "%s" does not exist.', $cartItem->productCode()));
        }

        $this->items->offsetUnset((string) $cartItem->productCode());
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProductCode(ProductCode $productCode): CartItem
    {
        if (!$this->items->offsetExists((string) $productCode)) {
            throw new CartItemNotFoundException(sprintf('Cart item with product code "%s" does not exist.', $productCode));
        }

        return $this->items->offsetGet((string) $productCode);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(CartItem $cartItem): bool
    {
        return $this->items->offsetExists((string) $cartItem->productCode());
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
