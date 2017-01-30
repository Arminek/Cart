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
final class CartItemAdded implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var CartItem
     */
    private $cartItem;

    /**
     * @param UuidInterface $cartId
     * @param CartItem $cartItem
     */
    private function __construct(UuidInterface $cartId, CartItem $cartItem)
    {
        $this->cartId = $cartId;
        $this->cartItem = clone $cartItem;
    }

    /**
     * @param UuidInterface $cartId
     * @param CartItem $cartItem
     *
     * @return CartItemAdded
     */
    public static function occur(UuidInterface $cartId, CartItem $cartItem): self
    {
        return new self($cartId, $cartItem);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return CartItem
     */
    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['cartId']),
            new CartItem(
                Uuid::fromString($data['cartItem']['cartItemId']),
                ProductCode::fromString($data['cartItem']['productCode']),
                CartItemQuantity::create($data['cartItem']['quantity']),
                new Money($data['cartItem']['unitPrice']['amount'], new Currency($data['cartItem']['unitPrice']['currency'])),
                new Money($data['cartItem']['subtotal']['amount'], new Currency($data['cartItem']['subtotal']['currency']))
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'cartId' => $this->cartId->toString(),
            'cartItem' => [
                'cartItemId' => $this->cartItem->cartItemId()->toString(),
                'productCode' => $this->cartItem->productCode()->__toString(),
                'quantity' => $this->cartItem->quantity()->getNumber(),
                'unitPrice' => $this->cartItem->unitPrice()->jsonSerialize(),
                'subtotal' => $this->cartItem->subtotal()->jsonSerialize()
            ]
        ];
    }
}
