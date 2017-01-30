<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class InitializeCart
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     */
    private function __construct(UuidInterface $cartId, string $currencyCode)
    {
        $this->cartId = $cartId;
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     *
     * @return InitializeCart
     */
    public static function create(UuidInterface $cartId, string $currencyCode): self
    {
        return new self($cartId, $currencyCode);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
