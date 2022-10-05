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
        if (!is_object($object) || !is_a($object, Event::class)) {
            throw new \Exception("Variable \$object must be an Event.");
        }

        return [
            'id' => $object->getId(),
            'type' => $object->getType(),
            //'actor' => $this->normalizer->normalize($object->actor()),
            'repo' => $this->normalizer->normalize($object->getRepo()),
            //'payload' => $object->payload(),
            'createAt' => $object->getCreateAt()->format('c'),
            'comment' => $object->getComment(),
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return is_object($data) && is_a($data, Event::class);
    }
}
