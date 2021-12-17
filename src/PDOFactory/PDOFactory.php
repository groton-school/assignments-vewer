<?php

namespace GrotonSchool\PDOFactory;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

abstract class PDOFactory
{
    /** @var PDO */
    private $pdo;

    /** @var string */
    protected $table;

    /** @var string */
    protected $objectType;

    public function __construct(PDO $pdo, string $table, string $objectType)
    {
        assert(!empty($pdo), new Exception('Valid PDO instance required'));
        assert(!empty($table), new Exception('Database table must be specified'));
        assert(!empty($objectType), new Exception('Factory product object type must be specified'));

        $this->pdo = $pdo;
        $this->table = $table;
        $this->objectType = $objectType;
    }

    public function getById($id, PDOObject $target = null): ?PDOObject
    {
        $statement = $this->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        if ($this->execute($statement, ['id' => $id])) {
            if (!$target) {
                $object = $statement->fetch();
                return $object ?: null;
            } else {
                $statement->setFetchMode(PDO::FETCH_INTO, $target);
                $statement->fetch();
                return $target;
            }
        }
    }

    public function create(array $data): PDOObject
    {
        $statement = $this->prepare("
            INSERT INTO {$this->table}
                (" . implode(', ', array_keys($data)) . ')
                VALUES (:' . implode(', :', array_keys($data)) . ')
        ');
        if ($this->execute($statement, $data)) {
            return $this->getById($this->pdo->lastInsertId());
        }
    }

    public function update(PDOObject $object, array $data): PDOObject
    {
        if (!empty($data)) {
            $propValuePair = function (string $prop): string {
                return "$prop = :$prop";
            };
            $statement = $this->prepare("
                UPDATE {$this->table}
                    SET " . implode(', ', array_map($propValuePair, array_keys($data))) . '
                    WHERE
                        id = :id
            ');
            $data['id'] = $object->getId();
            if ($this->execute($statement, $data)) {
                return $this->getById($object->getId(), $object);
            }
        }
        return $object;
    }

    public function delete(PDOObject $object, string $reason = null): bool
    {
        $statement = $this->prepare("
            DELETE FROM {$this->table}
                WHERE
                    id = :id
        ");
        // TODO log reason
        return $this->execute($statement, ['id' => $object->getId()]);
    }

    public function prepare(string $query): PDOStatement
    {
        $statement = $this->pdo->prepare($query);
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->objectType, [$this]);
        return $statement;
    }

    public function execute(PDOStatement $statement, array $params): bool
    {
        return $statement->execute($params);
    }
}
