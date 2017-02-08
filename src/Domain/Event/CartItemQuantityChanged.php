<?php

namespace SyliusCart\Domain\Event;

use Broadway\Serializer\SerializableInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\ValueObject\CartItemQuantity;
use SyliusCart\Domain\ValueObject\ProductCode;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartItemQuantityChanged implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var ProductCode
     */
    private $productCode;

    /**
     * @var CartItemQuantity
     */
    private $oldCartItemQuantity;

    /**
     * @var CartItemQuantity
     */
    private $newCartItemQuantity;

    /**
     * @param UuidInterface $cartId
     * @param ProductCode $productCode
     * @param CartItemQuantity $oldCartItemQuantity
     * @param CartItemQuantity $newCartItemQuantity
     */
    private function __construct(
        UuidInterface $cartId,
        ProductCode $productCode,
        CartItemQuantity $oldCartItemQuantity,
        CartItemQuantity $newCartItemQuantity
    ) {
        $this->cartId = $cartId;
        $this->productCode = $productCode;
        $this->oldCartItemQuantity = $oldCartItemQuantity;
        $this->newCartItemQuantity = $newCartItemQuantity;
    }

    /**
     * @param UuidInterface $cartId
     * @param ProductCode $productCode
     * @param CartItemQuantity $oldCartItemQuantity
     * @param CartItemQuantity $newCartItemQuantity
     *
     * @return CartItemQuantityChanged
     */
    public static function occur(
        UuidInterface $cartId,
        ProductCode $productCode,
        CartItemQuantity $oldCartItemQuantity,
        CartItemQuantity $newCartItemQuantity
    ): self {
        return new self($cartId, $productCode, $oldCartItemQuantity, $newCartItemQuantity);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return ProductCode
     */
    public function getProductCode(): ProductCode
    {
        return $this->productCode;
    }

    /**
     * @return CartItemQuantity
     */
    public function getOldCartItemQuantity(): CartItemQuantity
    {
        return $this->oldCartItemQuantity;
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
    public static function deserialize(array $data): self
    {
        return new self(
            Uuid::fromString($data['cartId']),
            ProductCode::fromString($data['productCode']),
            CartItemQuantity::create($data['oldCartItemQuantity']),
            CartItemQuantity::create($data['newCartItemQuantity'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'cartId' => (string) $this->cartId,
            'productCode' => (string) $this->productCode,
            'oldCartItemQuantity' => $this->oldCartItemQuantity->getNumber(),
            'newCartItemQuantity' => $this->newCartItemQuantity->getNumber(),
        ];
    }
}
