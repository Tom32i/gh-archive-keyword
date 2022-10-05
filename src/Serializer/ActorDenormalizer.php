<?php

namespace App\Serializer;

use App\Entity\Actor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ActorDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        if (!is_array($data)) {
            throw new \Exception('Expected array.');
        }

        return new Actor(
            (int) $data['id'],
            $data['login'],
            $data['url'],
            $data['avatar_url']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return is_a($type, Actor::class, true);
    }
}
