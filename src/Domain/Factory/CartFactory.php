<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Factory;

use Broadway\EventSourcing\AggregateFactory\AggregateFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
interface CartFactory extends AggregateFactoryInterface
{
    /**
     * @param UuidInterface $cartId
     * @param string $currencyCode
     *
     * @return CartContract
     */
    public function initialize(UuidInterface $cartId, string $currencyCode): CartContract;
}
