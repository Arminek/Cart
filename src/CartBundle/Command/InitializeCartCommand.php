<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\CommandHandling\CommandBusInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;
use SyliusCart\Domain\Command\InitializeCart;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class InitializeCartCommand extends Command
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
            ->setName('sylius:cart:initialize')
            ->setDescription('Initialize your event sourced cart')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Do you want init new cart or mocked one?',
            ['new', 'mocked'],
            0
        );

        $answer = $helper->ask($input, $output, $question);

        if ('mocked' === $answer) {
            $cartId = $this->uuidGenerator->generate();
        }

        if ('new' === $answer) {
            $cartId = Uuid::uuid4()->toString();
        }

        $currencyCode = $helper->ask($input, $output, new Question('Cart currency code: ', 'USD'));

        $initializeCart = InitializeCart::create(Uuid::fromString($cartId), $currencyCode);

        $this->commandBus->dispatch($initializeCart);

        $output->writeln(sprintf('Your cart id: "%s"', $cartId));
    }
}
