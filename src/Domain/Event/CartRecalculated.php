<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Broadway\Serializer\SerializableInterface;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartRecalculated implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var Money
     */
    private $newCartTotal;

    /**
     * @param UuidInterface $cartId
     * @param Money $newCartTotal
     */
    private function __construct(UuidInterface $cartId, Money $newCartTotal)
    {
        $this->cartId = $cartId;
        $this->newCartTotal = $newCartTotal;
    }

    /**
     * @param UuidInterface $cartId
     * @param Money $newCartTotal
     *
     * @return CartRecalculated
     */
    public static function occur(UuidInterface $cartId, Money $newCartTotal): self
    {
        return new self($cartId, $newCartTotal);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return Money
     */
    public function getNewCartTotal(): Money
    {
        return $this->newCartTotal;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['cartId']),
            new Money($data['total']['amount'], new Currency($data['total']['currency']))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'cartId' => $this->cartId->toString(),
            'total' => $this->newCartTotal->jsonSerialize()
        ];
    }
}
