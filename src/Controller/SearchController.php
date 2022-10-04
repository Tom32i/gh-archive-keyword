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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SearchController
{
    private ReadEventRepository $repository;
    private EventRepository $eventRepository;
    private SerializerInterface $serializer;

    public function __construct(
        ReadEventRepository $repository,
        EventRepository $eventRepository,
        SerializerInterface  $serializer
    ) {
        $this->repository = $repository;
        $this->eventRepository = $eventRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function searchCommits(Request $request): JsonResponse
    {
        try {
            $search = $this->serializer->denormalize($request->query->all(), SearchInput::class);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException('No search provided.');
        }

        $countByType = $this->eventRepository->countByType($search->date, $search->keyword);
        $total = $this->eventRepository->countAll($search->date, $search->keyword);
        $latest = $this->eventRepository->getLatest($search->date, $search->keyword);

        return new JsonResponse([
            'meta' => [
                'totalEvents' => $total,
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $this->serializer->normalize($latest),
                //'stats' => $this->repository->statsByTypePerHour($seach)
            ]
        ]);
    }
}
