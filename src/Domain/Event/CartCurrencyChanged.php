<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Event;

use Broadway\Serializer\SerializableInterface;
use Money\Currency;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartCurrencyChanged implements SerializableInterface
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * @var Currency
     */
    private $newCurrency;

    /**
     * @var Currency
     */
    private $oldCurrency;

    /**
     * @param UuidInterface $cartId
     * @param Currency $newCurrency
     * @param Currency $oldCurrency
     */
    private function __construct(UuidInterface $cartId, Currency $newCurrency, Currency $oldCurrency)
    {
        $this->cartId = $cartId;
        $this->newCurrency = $newCurrency;
        $this->oldCurrency = $oldCurrency;
    }

    /**
     * @param UuidInterface $cartId
     * @param Currency $newCurrency
     * @param Currency $oldCurrency
     *
     * @return CartCurrencyChanged
     */
    public static function occur(UuidInterface $cartId, Currency $newCurrency, Currency $oldCurrency): self
    {
        return new self($cartId, $newCurrency, $oldCurrency);
    }

    /**
     * @return UuidInterface
     */
    public function getCartId(): UuidInterface
    {
        return $this->cartId;
    }

    /**
     * @return Currency
     */
    public function getNewCurrency(): Currency
    {
        return $this->newCurrency;
    }

    /**
     * @return Currency
     */
    public function getOldCurrency(): Currency
    {
        return $this->oldCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['cartId']),
            new Currency($data['newCurrency']),
            new Currency($data['oldCurrency'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return [
            'cartId' => $this->cartId->toString(),
            'newCurrency' => $this->newCurrency->jsonSerialize(),
            'oldCurrency' => $this->oldCurrency->jsonSerialize(),
        ];
    }
}
