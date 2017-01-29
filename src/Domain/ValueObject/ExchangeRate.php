<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\ValueObject;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ExchangeRate
{


    /**
     * @param float $value
     * @param \DateTimeInterface|null $date
     */
    private function __construct($value, \DateTimeInterface $date = null)
    {
        $this->value = (string) $value;
        $this->date = $date ?: new \DateTime();
    }

    /**
     * @param float $value
     * @param \DateTimeInterface|null $date
     *
     * @return ExchangeRate
     */
    public static function create(float $value, \DateTimeInterface $date = null): self
    {
        return new self($value, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        return $this->date;
    }
}
