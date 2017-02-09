<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ChangeProductQuantity
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
    private $newQuantity;

    /**
     * @param string $cartId
     * @param string $productCode
     * @param int $newQuantity
     */
    private function __construct(string $cartId, string $productCode, int $newQuantity)
    {
        $this->cartId = $cartId;
        $this->productCode = $productCode;
        $this->newQuantity = $newQuantity;
    }

    /**
     * @param string $cartId
     * @param string $productCode
     * @param int $newQuantity
     *
     * @return self
     */
    public static function create(string $cartId, string $productCode, int $newQuantity): self
    {
        return new self($cartId, $productCode, $newQuantity);
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
    public function getNewQuantity(): int
    {
        return $this->newQuantity;
    }
}
