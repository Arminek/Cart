<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Broadway\Serializer\SerializableInterface;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Model\CartItem;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemRemoved implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var UuidInterface
     */
    private $cartItemId;

    /**
     * @param UuidInterface $cartId
     * @param UuidInterface $cartItemId
     */
    private function __construct(UuidInterface $cartId, UuidInterface $cartItemId)
    {
        $this->cartId = $cartId;
        $this->cartItemId = $cartItemId;
    }

    /**
     * @param UuidInterface $cartId
     * @param UuidInterface $cartItemId
     *
     * @return CartItemRemoved
     */
    public static function occur(UuidInterface $cartId, UuidInterface $cartItemId): self
    {
        return new self($cartId, $cartItemId);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return UuidInterface
     */
    public function getCartItemId(): UuidInterface
    {
        return $this->cartItemId;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['cartId']),
            Uuid::fromString($data['cartItemId'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'cartId' => $this->cartId->toString(),
            'cartItemId' => $this->cartItemId->toString()
        ];
    }
}
