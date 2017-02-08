<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Broadway\Serializer\SerializableInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
     * @var ProductCode
     */
    private $productCode;

    /**
     * @param UuidInterface $cartId
     * @param ProductCode $productCode
     */
    private function __construct(UuidInterface $cartId, ProductCode $productCode)
    {
        $this->cartId = $cartId;
        $this->productCode = $productCode;
    }

    /**
     * @param UuidInterface $cartId
     * @param ProductCode $productCode
     *
     * @return self
     */
    public static function occur(UuidInterface $cartId, ProductCode $productCode): self
    {
        return new self($cartId, $productCode);
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
     * {@inheritdoc}
     */
    public static function deserialize(array $data): self
    {
        return new self(
            Uuid::fromString($data['cartId']),
            ProductCode::fromString($data['productCode'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): array
    {
        return [
            'cartId' => (string) $this->cartId,
            'productCode' => (string) $this->productCode
        ];
    }
}
