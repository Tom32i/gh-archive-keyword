<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Webmozart\Assert\Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: '`event`')]
#[ORM\Index(name: 'IDX_EVENT_TYPE', columns: ['type'])]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $id;

    #[ORM\Column(type: 'EventType')]
    private string $type;

    #[ORM\Column(type: Types::INTEGER)]
    private int $count = 1;

    #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id')]
    private Actor $actor;

    #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id')]
    private Repo $repo;

    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    private array $payload;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createAt;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment;

    public function __construct(
        int $id,
        string $type,
        Actor $actor,
        Repo $repo,
        array $payload,
        \DateTimeImmutable $createAt,
        ?string $comment = null
    ) {
        $this->id = $id;
        EventType::assertValidChoice($type);
        $this->type = $type;
        $this->actor = $actor;
        $this->repo = $repo;
        $this->payload = $payload;
        $this->createAt = $createAt;
        $this->comment = $comment;

        if ($type === EventType::COMMIT) {
            $this->count = $payload['size'] ?? 1;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getActor(): Actor
    {
        return $this->actor;
    }

    public function getRepo(): Repo
    {
        return $this->repo;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreateAt(): \DateTimeImmutable
    {
        return $this->createAt;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
