<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Entity\EventType;
use App\Repository\EventRepository;
use App\Repository\ReadEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchController
{
    public function __construct(
        private EventRepository $eventRepository,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'], format: 'json' )]
    public function searchCommits(Request $request): JsonResponse
    {
        \assert($this->serializer instanceof NormalizerInterface);
        \assert($this->serializer instanceof DenormalizerInterface);

        try {
            $search = $this->serializer->denormalize($request->query->all(), SearchInput::class);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException('No search provided.', $exception);
        }

        $countByType = $this->eventRepository->countByType($search->date, $search->keyword);
        $total = $this->eventRepository->countAll($search->date, $search->keyword);
        $latest = $this->eventRepository->getLatest($search->date, $search->keyword);
        $stats = $this->eventRepository->statsByTypePerHour($search->date, $search->keyword);

        return new JsonResponse([
            'meta' => [
                'totalEvents' => $total,
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $this->serializer->normalize($latest),
                'stats' => $stats
            ]
        ]);
    }
}
