<?php

namespace GrotonSchool\AssignmentsViewer\Users;

use Battis\PDOFactory\PDOFactory;
use PDO;

/**
 * @method User getById(string|integer $id)
 * @method User create(array $data)
 * @method User update(array $data)
 */
class UserFactory extends PDOFactory
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'users', User::class);
    }

    public function getByInstance(string $toolConsumerInstanceGuid, string $userId): User
    {
        $statement = $this->prepare("
            SELECT * FROM {$this->table}
                WHERE
                    tool_consumer_instance_guid = :tool_consumer_instance_guid AND
                    user_id = :user_id
                LIMIT 1
        ");
        $data = [
            'tool_consumer_instance_guid' => $toolConsumerInstanceGuid,
            'user_id' => $userId
        ];
        if ($this->execute($statement, $data)) {
            $user = $statement->fetch();
            if ($user) {
                return $user;
            } else {
                return $this->create($data);
            }
        }
    }
}
