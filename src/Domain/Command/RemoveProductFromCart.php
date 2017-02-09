<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class RemoveProductFromCart
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
     * @param string $cartId
     * @param string $productCode
     */
    private function __construct(string $cartId, string $productCode)
    {
        $this->cartId = $cartId;
        $this->productCode = $productCode;
    }

    /**
     * @param string $cartId
     * @param string $productCode
     *
     * @return self
     */
    public static function create(string $cartId, string $productCode): self
    {
        return new self($cartId, $productCode);
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
}
