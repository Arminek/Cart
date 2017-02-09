<?php

namespace SyliusCart\CartBundle\Command;

use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStoreInterface;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class ShowRecordedEvents extends Command
{
    /**
     * @var EventStoreInterface
     */
    private $eventStore;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @param EventStoreInterface $eventStore
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(EventStoreInterface $eventStore, UuidGeneratorInterface $uuidGenerator)
    {
        $this->eventStore = $eventStore;
        $this->uuidGenerator = $uuidGenerator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('sylius:event-store:load')
            ->setDescription('Debug command to see all recorded events.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
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

        $domainEventStream = $this->eventStore->load($cartId);

        /** @var DomainMessage $event */
        foreach ($domainEventStream as $event) {
            $table = new Table($output);
            $table
                ->setHeaders(['Recorded on', 'Type', 'Payload'])
                ->setRows([[$event->getRecordedOn()->toString(), $event->getType(), $this->implodeRecursively($event->getPayload()->serialize())]])
            ;

            $table->render();
        }
    }

    /**
     * @param array $payload
     *
     * @return string
     */
    private function implodeRecursively(array $payload): string
    {
        foreach ($payload as $key => $data) {
            if (is_array($data)) {
                try {
                    $payload[$key] = implode(' ', $data);
                } catch (\Exception $e) {
                    $payload[$key] = $this->implodeRecursively($payload[$key]);
                }
            }
        }

        return implode(' ', $payload);
    }
}
