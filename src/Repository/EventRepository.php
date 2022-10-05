<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function persist(Event $event, bool $flush = false): void
    {
        $this->getEntityManager()->persist($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countAll(\DateTimeInterface $date, string $keyword): int
    {
        return intval($this->getQueryBuilder($date, $keyword)
            ->select('SUM(event.count) as count')
            ->getQuery()
            ->getSingleScalarResult());
    }

    public function countByType(\DateTimeInterface $date, string $keyword): array
    {
        $results = $this->getQueryBuilder($date, $keyword)
            ->select('event.type as type, SUM(event.count) as count')
            ->groupBy('event.type')
            ->getQuery()
            ->getArrayResult();

        return array_combine(
            array_map(fn (array $result) => $result['type'], $results),
            array_map(fn (array $result) => $result['count'], $results)
        );
    }

    /**
     * @return Event[]
     */
    public function getLatest(\DateTimeInterface $date, string $keyword): array
    {
        return $this->getQueryBuilder($date, $keyword)
            ->select('event, repo')
            ->join('event.repo', 'repo')
            ->getQuery()
            ->getResult();
    }

    public function exists(int $id): bool
    {
        return $this->createQueryBuilder('event')
            ->select('count(event.id)')
            ->where('event.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult() === 1;
    }

    public function getQueryBuilder(\DateTimeInterface $date, string $keyword): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->andWhere('DATE(event.createAt) = :date')
            ->andWhere('CAST(event.payload AS TEXT) LIKE :keyword')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('keyword', "%$keyword%");
    }
}
