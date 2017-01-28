<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Exception;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartAlreadyEmptyException extends \DomainException
{
    /**
     * @param string $message
     * @param \Exception|null $previousException
     */
    public function __construct(string $message = 'Cart is already empty.', \Exception $previousException = null)
    {
        parent::__construct($message, 0, $previousException);
    }
}
