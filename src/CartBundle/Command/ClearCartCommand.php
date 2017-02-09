<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use SyliusCart\Domain\Command\ClearCart;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ClearCartCommand extends Command
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
            ->setName('sylius:cart:clear')
            ->setDescription('Clear cart')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $cartId = $helper->ask(
            $input,
            $output,
            new Question(
                sprintf('Cart id (%s): ', $this->uuidGenerator->generate()),
                $this->uuidGenerator->generate()
            )
        );

        $clearCart = ClearCart::create($cartId);

        $this->commandBus->dispatch($clearCart);

        $output->writeln(sprintf('Cart cleared!'));
    }
}
