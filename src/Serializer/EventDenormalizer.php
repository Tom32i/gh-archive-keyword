<?php

namespace App\Serializer;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Repository\ActorRepository;
use App\Repository\RepoRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EventDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private ActorRepository $actorRepository,
        private RepoRepository $repoRepository,
    )
    {
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        $type = EventType::getFromGHArchive($data['type']);

        if ($type === null) {
            return null;
        }

        return new Event(
            (int) $data['id'],
            $type,
            $this->getActor($data['actor']),
            $this->getRepo($data['repo']),
            $data['payload'],
            new \DateTimeImmutable($data['created_at']),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return is_a($type, Event::class, true);
    }

    private function getActor(array $data): Actor
    {
        $actor = $this->actorRepository->findOneById($data['id']);

        if ($actor !== null) {
            return $actor;
        }

        return $this->denormalizer->denormalize($data, Actor::class);
    }

    private function getRepo(array $data): Repo
    {
        $repo = $this->repoRepository->findOneById($data['id']);

        if ($repo !== null) {
            return $repo;
        }

        return $this->denormalizer->denormalize($data, Repo::class);
    }
}
