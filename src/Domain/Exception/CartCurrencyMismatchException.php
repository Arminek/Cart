<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\Exception;

use Money\Currency;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class CartCurrencyMismatchException extends \DomainException
{
    /**
     * @param Currency $expectedCurrency
     * @param Currency $unExpectedCurrency
     * @param \Exception|null $previousException
     */
    public function __construct(Currency $expectedCurrency, Currency $unExpectedCurrency, \Exception $previousException = null)
    {
        $message = sprintf(
            'Trying to add cart item in different currency. Expected: "%s", got "%s"',
            $expectedCurrency->getCode(),
            $unExpectedCurrency->getCode()
        );

        parent::__construct($message, 0, $previousException);
    }
}
