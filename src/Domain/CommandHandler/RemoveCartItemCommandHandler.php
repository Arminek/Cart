<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use SyliusCart\Domain\Command\RemoveCartItem;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class RemoveCartItemCommandHandler extends CommandHandler
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
     * @param RemoveCartItem $command
     */
    public function handleRemoveCartItem(RemoveCartItem $command): void
    {
        /** @var CartContract $cart */
        $cart = $this->cartRepository->load($command->getCartId());

        $cart->removeCartItem($command->getCartItemId());

        $this->cartRepository->save($cart);
    }
}
