<?php

declare(strict_types = 1);

namespace SyliusCart\Domain\CommandHandler;

use Broadway\CommandHandling\CommandHandler;
use Broadway\Repository\RepositoryInterface;
use SyliusCart\Domain\Command\AddCartItem;
use SyliusCart\Domain\Model\CartContract;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class AddCartItemCommandHandler extends CommandHandler
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
     * @param AddCartItem $command
     */
    public function handleAddCartItem(AddCartItem $command): void
    {
        /** @var CartContract $cart */
        $cart = $this->cartRepository->load($command->getCartId());

        $cart->addCartItem($command->getProductCode(), $command->getQuantity(), $command->getPrice(), $command->getProductCurrencyCode());

        $this->cartRepository->save($cart);
    }
}
