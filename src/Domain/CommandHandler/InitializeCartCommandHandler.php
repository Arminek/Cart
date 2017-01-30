<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use SyliusCart\Domain\Command\InitializeCart;
use SyliusCart\Domain\Factory\CartFactory;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class InitializeCartCommandHandler extends CommandHandler
{
    /**
     * @var RepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartFactory
     */
    private $cartFactory;

    /**
     * @param RepositoryInterface $cartRepository
     * @param CartFactory $cartFactory
     */
    public function __construct(RepositoryInterface $cartRepository, CartFactory $cartFactory)
    {
        $this->cartRepository = $cartRepository;
        $this->cartFactory = $cartFactory;
    }

    /**
     * @param InitializeCart $command
     */
    public function handleInitializeCart(InitializeCart $command): void
    {
        $cart = $this->cartFactory->initialize($command->getCartId(), $command->getCurrencyCode());
        $this->cartRepository->save($cart);
    }
}
