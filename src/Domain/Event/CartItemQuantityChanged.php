<?php

namespace SyliusCart\Domain\Event;

use Broadway\Serializer\SerializableInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\ValueObject\CartItemQuantity;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemQuantityChanged implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartItemId;

    /**
     * @var CartItemQuantity
     */
    private $newCartItemQuantity;

    /**
     * @param UuidInterface $cartItemId
     * @param CartItemQuantity $newCartItemQuantity
     */
    private function __construct(UuidInterface $cartItemId, CartItemQuantity $newCartItemQuantity)
    {
        $this->cartItemId = $cartItemId;
        $this->newCartItemQuantity = $newCartItemQuantity;
    }

    /**
     * @param UuidInterface $cartItemId
     * @param CartItemQuantity $newCartItemQuantity
     *
     * @return CartItemQuantityChanged
     */
    public static function occur(UuidInterface $cartItemId, CartItemQuantity $newCartItemQuantity): self
    {
        return new self($cartItemId, $newCartItemQuantity);
    }

    /**
     * @return UuidInterface
     */
    public function getCartItemId(): UuidInterface
    {
        return $this->cartItemId;
    }

    /**
     * @return CartItemQuantity
     */
    public function getNewCartItemQuantity(): CartItemQuantity
    {
        return $this->newCartItemQuantity;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['cartItemId']),
            CartItemQuantity::create($data['newCartItemQuantity'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'cartItemId' => $this->cartItemId->toString(),
            'newCartItemQuantity' => $this->newCartItemQuantity->getNumber(),
        ];
    }
}
