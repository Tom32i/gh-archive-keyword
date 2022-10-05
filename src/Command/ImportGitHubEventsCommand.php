<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This command must import GitHub events.
 * You can add the parameters and code you want in this command to meet the need.
 */
#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events',
)]
class ImportGitHubEventsCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
        private SerializerInterface $serializer,
        private EventRepository $eventRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('date', InputArgument::OPTIONAL, 'Date and time', '')
            ->setHelp('php -d memory_limit=512M bin/console app:import-github-events "2015-01-01 12:00"')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = new \DateTimeImmutable($input->getArgument('date'));
        $separator = "\n";
        $clearEvery = 100;

        $io->title("Importing events for {$date->format('Y-m-d H:00')} :");

        $content = $this->getJsonContent($date);

        if ($content === null) {
            $io->error('Could not fetch content form GHArchive.');

            return static::FAILURE;
        }

        $total = substr_count($content, $separator);
        $parts = explode($separator, $content);
        $insert = 0;

        unset($content);

        $io->progressStart(count($parts));

        while ($part = array_shift($parts)) {
            if ($this->parseEvent($part) && ($insert++ % $clearEvery == 0)) {
                $this->entityManager->clear();
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success("Done! $insert events imported.");

        return static::SUCCESS;
    }

    private function getJsonContent(\DateTimeInterface $date): ?string
    {
        $response = $this->client->request('GET', $this->getUrl($date));

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return gzdecode($response->getContent());
    }

    private function parseEvent(string $value): bool
    {
        try {
            $event = $this->serializer->deserialize($value, Event::class, 'json');
        } catch (\Exception $exception) {
            return false;
        }

        if ($this->eventRepository->exists($event->getId())) {
            return false;
        }

        $this->eventRepository->persist($event, true);

        return true;
    }

    public function getUrl(\DateTimeInterface $date): string
    {
        return "http://data.gharchive.org/{$date->format('Y-m-d-G')}.json.gz";
    }
}
