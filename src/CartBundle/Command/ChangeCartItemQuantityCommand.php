<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use SyliusCart\Domain\Command\ChangeCartItemQuantity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ChangeCartItemQuantityCommand extends Command
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
            ->setName('sylius:cart:item-quantity-change')
            ->setDescription('Change cart item quantity')
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
        $quantityQuestion = new Question('New quantity: ', 1);

        $cartItemId = $helper->ask($input, $output, $cartItemIdQuestion);
        $quantity = $helper->ask($input, $output, $quantityQuestion);

        $cartId = $this->uuidGenerator->generate();
        $changeCartItemQuantity = ChangeCartItemQuantity::create($cartId, $cartItemId, $quantity);

        $this->commandBus->dispatch($changeCartItemQuantity);

        $output->writeln(sprintf('Quantity changed!'));
    }
}