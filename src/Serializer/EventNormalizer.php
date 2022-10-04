<?php

namespace App\Serializer;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Repo;
use App\Repository\ActorRepository;
use App\Repository\RepoRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        return [
            'id' => $object->id(),
            'type' => $object->type(),
            //'actor' => $this->normalizer->normalize($object->actor()),
            'repo' => $this->normalizer->normalize($object->repo()),
            //'payload' => $object->payload(),
            'createAt' => $object->createAt()->format('c'),
            'comment' => $object->getComment(),
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return is_a($data, Event::class);
    }
}
