<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class AddCartItem
{
    /**
     * @var string
     */
    private $cartId;

    /**
     * @var string
     */
    private $productCode;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var int
     */
    private $price;

    /**
     * @var string
     */
    private $productCurrencyCode;

    /**
     * @param string $cartId
     * @param string $productCode
     * @param int $quantity
     * @param int $price
     * @param string $productCurrencyCode
     */
    private function __construct(
        string $cartId,
        string $productCode,
        int $quantity,
        int $price,
        string $productCurrencyCode
    ) {
        $this->cartId = $cartId;
        $this->productCode = $productCode;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->productCurrencyCode = $productCurrencyCode;
    }

    /**
     * @param string $cartId
     * @param string $productCode
     * @param int $quantity
     * @param int $price
     * @param string $productCurrencyCode
     *
     * @return AddCartItem
     */
    public static function create(
        string $cartId,
        string $productCode,
        int $quantity,
        int $price,
        string $productCurrencyCode
    ): self {
        return new self($cartId, $productCode, $quantity, $price, $productCurrencyCode);
    }

    /**
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->productCode;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getProductCurrencyCode(): string
    {
        return $this->productCurrencyCode;
    }
}
