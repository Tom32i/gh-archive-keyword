<?php

namespace App\Serializer;

use App\Dto\SearchInput;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SearchInputDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (!is_array($data)) {
            throw new \Exception('Expected array.');
        }

        return new SearchInput(
            new \DateTimeImmutable((string) $data['date']),
            (string) $data['keyword'],
        );
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return is_a($type, SearchInput::class, true);
    }
}
