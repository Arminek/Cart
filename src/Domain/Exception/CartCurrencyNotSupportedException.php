<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Exception;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartCurrencyNotSupportedException extends \DomainException
{
    /**
     * @param string $message
     * @param \Exception|null $previousException
     */
    public function __construct($message = 'Currency code not supported.', \Exception $previousException = null)
    {
        parent::__construct($message, 0, $previousException);
    }
}
