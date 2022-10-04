<?php

namespace App\Serializer;

use App\Entity\Repo;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RepoDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    {
        return new Repo(
            (int) $data['id'],
            $data['name'],
            $data['url']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return is_a($type, Repo::class, true);
    }
}
