<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use SyliusCart\Domain\Command\AddProductToCart;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class AddProductToCartCommand extends Command
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
            ->setName('sylius:cart:add-product')
            ->setDescription('Add product to cart')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $cartId = $helper->ask(
            $input,
            $output,
            new Question(
                sprintf('Cart id (%s): ', $this->uuidGenerator->generate()),
                $this->uuidGenerator->generate()
            )
        );

        $productCode = $helper->ask($input, $output, new Question('Product code: ', 'Mug'));
        $quantity = $helper->ask($input, $output, new Question('Quantity: ', 1));
        $price = $helper->ask($input, $output, new Question('How much does it costs in cents: ', 1000));
        $currency = $helper->ask($input, $output, new Question('In which currency: ', 'EUR'));

        $addCartItem = AddProductToCart::create($cartId, $productCode, $quantity, $price, $currency);

        $this->commandBus->dispatch($addCartItem);

        $output->writeln(sprintf('Product added!'));
    }
}
