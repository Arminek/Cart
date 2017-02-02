<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Factory;

use Broadway\Domain\DomainEventStreamInterface;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Adapter\AvailableCurrencies\AvailableCurrenciesProviderInterface;
use SyliusCart\Domain\Model\Cart;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartAggregateFactory implements CartFactory
{
    /**
     * @var AvailableCurrenciesProviderInterface
     */
    private $availableCurrenciesProvider;

    /**
     * @param AvailableCurrenciesProviderInterface $availableCurrenciesProvider
     */
    public function __construct(AvailableCurrenciesProviderInterface $availableCurrenciesProvider)
    {
        $this->availableCurrenciesProvider = $availableCurrenciesProvider;
    }

    /**
     * @param string $aggregateClass
     * @param DomainEventStreamInterface $domainEventStream
     *
     * @return CartContract
     */
    public function create($aggregateClass, DomainEventStreamInterface $domainEventStream): CartContract
    {
        $cart = Cart::createWithAdapters($this->availableCurrenciesProvider);

        $cart->initializeState($domainEventStream);

        return $cart;
    }

    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     *
     * @return CartContract
     */
    public function initialize(UuidInterface $cartId, string $currencyCode): CartContract
    {
        return Cart::initialize($cartId, $currencyCode, $this->availableCurrenciesProvider);
    }
}
