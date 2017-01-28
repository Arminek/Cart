<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\ValueObject;

use SyliusCart\Domain\Exception\ProductCodeCannotBeEmptyException;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ProductCode
{
    /**
     * @var string
     */
    private $code;

    /**
     * @param string $code
     */
    private function __construct(string $code)
    {
        if ('' === $code) {
            throw new ProductCodeCannotBeEmptyException();
        }

        $this->code = $code;
    }

    /**
     * @param string $code
     *
     * @return self
     */
    public static function fromString($code): self
    {
        return new self($code);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->code;
    }

    /**
     * @param ProductCode $productCode
     *
     * @return bool
     */
    public function equals(ProductCode $productCode): bool
    {
        return  $productCode->__toString() === $this->code;
    }
}
