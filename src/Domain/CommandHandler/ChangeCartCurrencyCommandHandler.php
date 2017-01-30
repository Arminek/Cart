<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use SyliusCart\Domain\Command\ChangeCartCurrency;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ChangeCartCurrencyCommandHandler extends CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    private $cartRepository;

    /**
     * @param RepositoryInterface $cartRepository
     */
    public function __construct(RepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param ChangeCartCurrency $command
     */
    public function handleChangeCartCurrency(ChangeCartCurrency $command): void
    {
        /** @var CartContract $cart */
        $cart = $this->cartRepository->load($command->getCartId());

        $cart->changeCurrency($command->getCurrencyCode());

        $this->cartRepository->save($cart);
    }
}
