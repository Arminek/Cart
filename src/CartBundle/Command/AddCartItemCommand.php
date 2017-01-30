<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use SyliusCart\Domain\Command\AddCartItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class AddCartItemCommand extends Command
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
            ->setName('sylius:cart:add-cart-item')
            ->setDescription('Add cart item')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $productCodeQuestion = new Question('Product code: ', 'Mug');
        $quantityQuestion = new Question('Quantity: ', 1);
        $priceQuestion = new Question('How much does it costs in cents: ', 1000);
        $currencyQuestion = new Question('In which currency: ', 'EUR');

        $productCode = $helper->ask($input, $output, $productCodeQuestion);
        $quantity = $helper->ask($input, $output, $quantityQuestion);
        $price = $helper->ask($input, $output, $priceQuestion);
        $currency = $helper->ask($input, $output, $currencyQuestion);

        $cartId = $this->uuidGenerator->generate();
        $addCartItem = AddCartItem::create($cartId, $productCode, $quantity, $price, $currency);

        $this->commandBus->dispatch($addCartItem);

        $output->writeln(sprintf('Product added!'));
    }
}
