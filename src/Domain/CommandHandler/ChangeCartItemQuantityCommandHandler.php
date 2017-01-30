<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use SyliusCart\Domain\Command\ChangeCartItemQuantity;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ChangeCartItemQuantityCommandHandler extends CommandHandler
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
     * @param ChangeCartItemQuantity $command
     */
    public function handleChangeCartItemQuantity(ChangeCartItemQuantity $command): void
    {
        /** @var CartContract $cart */
        $cart = $this->cartRepository->load($command->getCartId());

        $cart->changeCartItemQuantity($command->getCartItemId(), $command->getQuantity());

        $this->cartRepository->save($cart);
    }
}
