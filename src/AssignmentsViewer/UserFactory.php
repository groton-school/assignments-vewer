<?php

namespace GrotonSchool\AssignmentsViewer;

use Exception;
use GrotonSchool\PDOFactory\PDOFactory;
use PDO;

/**
 * @method User|null getById(string|integer$id)
 * @method User create(array<string,mixed> $data)
 * @method User updateById(string|integer $id, array<string,mixed> $data)
 */
class UserFactory extends PDOFactory
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, User::class, 'users');
    }

    public function getByUserId($userId, $toolConsumerInstanceGUID): ?User
    {
        $statement = $this->pdo->prepare('
            SELECT *
                FROM users
                WHERE
                    user_id = :user_id AND
                    tool_consumer_instance_guid = :tool_consumer_instance_guid
                LIMIT 1
        ');
        if ($statement->execute(['user_id' => $userId, 'tool_consumer_instance_guid' => $toolConsumerInstanceGUID])) {
            $user = $statement->fetchObject(User::class);
            return $user ?: null;
        }
        return null;
    }

    public function updateByUserId($userId, $toolConsumerInstanceGUID, array $data): User
    {
        $statement = $this->pdo->prepare('
            UPDATE users
                SET ' .
                implode(', ', array_map(
                    function ($elt) {
                        return "$elt = :$elt";
                    },
                    array_keys($data)
                )) . '
                WHERE
                    user_id = :user_id AND
                    tool_consumer_instance_guid = :tool_consumer_instance_guid
        ');
        if ($statement->execute(array_merge(
            $data,
            ['user_id' => $userId, 'tool_consumer_instance_guid' => $toolConsumerInstanceGUID]
        ))) {
            return $this->getByUserId($userId, $toolConsumerInstanceGUID);
        } else {
            throw new Exception(implode(PHP_EOL, $this->pdo->errorInfo()), $this->pdo->errorCode());
        }
    }
}
