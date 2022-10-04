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
            ->addArgument('date', InputArgument::OPTIONAL, 'Your last name?', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = new \DateTimeImmutable($input->getArgument('date'));
        $separator = "\n";
        $flushEvery = 10;

        $io->title("Importing events for {$date->format('Y-m-d H:00')} :");

        $content = $this->getJsonContent($date);

        if (!$content) {
            $io->error('Could not fetch content form GHArchive.');

            return static::FAILURE;
        }

        $total = substr_count($content, $separator);
        $part = strtok($content, $separator);
        $insert = 0;

        $io->progressStart($total);

        while ($part !== false) {
            if ($this->parseEvent($part)) {
                if ($insert++ % $flushEvery == 0) {
                    $this->entityManager->clear();
                }
            }

            $part = strtok($separator);
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
        $event = $this->serializer->deserialize($value, Event::class, 'json');

        if ($event === null) {
            return false;
        }

        if ($this->eventRepository->countById($event->id()) > 0) {
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
