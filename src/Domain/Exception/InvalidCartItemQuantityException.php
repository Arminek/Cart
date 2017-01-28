<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Exception;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class InvalidCartItemQuantityException extends \DomainException
{
    /**
     * @param string $message
     * @param \Exception|null $previousException
     */
    public function __construct(string $message = 'Invalid cart item quantity.', \Exception $previousException = null)
    {
        parent::__construct($message, 0, $previousException);
    }
}
