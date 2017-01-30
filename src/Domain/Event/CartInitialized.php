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
final class CartInitialized implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var Money
     */
    private $cartTotal;

    /**
     * @param UuidInterface $cartId
     * @param Money $cartTotal
     */
    private function __construct(UuidInterface $cartId, Money $cartTotal)
    {
        $this->cartId = $cartId;
        $this->cartTotal = $cartTotal;
    }

    /**
     * @param UuidInterface $cartId
     * @param Money $cartTotal
     *
     * @return self
     */
    public static function occur(UuidInterface $cartId, Money $cartTotal): self
    {
        return new self($cartId, $cartTotal);
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
    public function getCartTotal(): Money
    {
        return $this->cartTotal;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['cartId']),
            new Money($data['cartTotal']['amount'], new Currency($data['cartTotal']['currency']))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'cartId' => $this->cartId->toString(),
            'cartTotal' => $this->cartTotal->jsonSerialize()
        ];
    }
}
