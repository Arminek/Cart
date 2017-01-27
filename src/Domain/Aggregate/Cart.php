<?php

namespace SyliusCart\Domain\Aggregate;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class Cart extends EventSourcedAggregateRoot
{
    /**
     * @var UuidInterface
     */
    private $cartId;

    /**
     * {@inheritdoc}
     */
    public function getAggregateRootId(): UuidInterface
    {
        return $this->cartId;
    }
}
