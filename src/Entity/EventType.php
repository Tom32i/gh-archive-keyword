<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT = 'COM';
    public const COMMENT = 'MSG';
    public const PULL_REQUEST = 'PR';

    protected static array $choices = [
        self::COMMIT => 'Commit',
        self::COMMENT => 'Comment',
        self::PULL_REQUEST => 'Pull Request',
    ];

    public static function getFromGHArchive(string $value)
    {
        switch ($value) {
            case 'PullRequestEvent':
                return self::PULL_REQUEST;

            case 'CommitCommentEvent':
            case 'IssueCommentEvent':
                return self::COMMENT;

            case 'PushEvent':
                return self::COMMIT;

            default:
                return null;
        }
    }
}
