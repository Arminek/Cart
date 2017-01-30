<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use SyliusCart\Domain\Command\RemoveCartItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class RemoveCartItemCommand extends Command
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @param CommandBusInterface $commandBus
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(CommandBusInterface $commandBus, UuidGeneratorInterface $uuidGenerator)
    {
        $this->commandBus = $commandBus;
        $this->uuidGenerator = $uuidGenerator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sylius:cart:remove-cart-item')
            ->setDescription('Remove cart item')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $cartItemIdQuestion = new Question('Cart item id: ');

        $cartItemId = $helper->ask($input, $output, $cartItemIdQuestion);

        $cartId = $this->uuidGenerator->generate();
        $cartItemRemove = RemoveCartItem::create($cartId, $cartItemId);

        $this->commandBus->dispatch($cartItemRemove);

        $output->writeln(sprintf('Item removed!'));
    }
}
