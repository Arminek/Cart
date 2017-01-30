<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use SyliusCart\Domain\Command\ClearCart;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ClearCartCommandHandler extends CommandHandler
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
     * @param ClearCart $command
     */
    public function handleClearCart(ClearCart $command): void
    {
        /** @var CartContract $cart */
        $cart = $this->cartRepository->load($command->getCartId());

        $cart->clear();

        $this->cartRepository->save($cart);
    }
}
