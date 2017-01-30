<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Command;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ChangeCartCurrency
{
    /**
     * @var string
     */
    private $cartId;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @param string $cartId
     * @param string $currencyCode
     */
    private function __construct(string $cartId, string $currencyCode)
    {
        $this->cartId = $cartId;
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param string $cartId
     * @param string $currencyCode
     *
     * @return ChangeCartCurrency
     */
    public static function create(string $cartId, string $currencyCode): self
    {
        return new self($cartId, $currencyCode);
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
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }
}
