<?php

namespace GrotonSchool\PDOFactory;

use Exception;
use PDO;

abstract class PDOFactory
{
    /** @var PDO */
    protected $pdo;

    /** @var string */
    protected $table;

    /** @var string */
    protected $productType;

    /**
     * @param PDO $pdo
     * @param string|null $table
     * @return void
     */
    public function __construct(PDO $pdo, $productType, $table)
    {
        $this->pdo = $pdo;

        if (empty($productType)) {
            $this->productType = preg_replace('/Factory$/', '', get_class($this));
        } else {
            $this->productType = $productType;
        }

        if (empty($table)) {
            $this->table = strtolower(basename($this->productType));
        } else {
            $this->table = $table;
        }
    }

    /**
     * @param string|integer $id
     * @return null|PDOObject
     */
    public function getById($id): ?PDOObject
    {
        assert($this->pdo);
        assert($this->table);
        assert($this->productType);

        $product = null;

        $select = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        if ($select->execute(['id' => $id])) {
            $productType = $this->productType;
            $product = new $productType();

            $product->pdo = $this->pdo;
            
            $select->setFetchMode(PDO::FETCH_INTO, $product);
            $select->fetch();
        }

        return $product;
    }

    /**
     * @param array<string,mixed> $data
     * @return PDOObject
     */
    public function create(array $data): PDOObject
    {
        assert($this->pdo);
        assert($this->table);

        $statement = $this->pdo->prepare("INSERT INTO {$this->table} (" . implode(', ', array_keys($data)) .') VALUES (:' . implode(', :', array_keys($data)) . ')');
        if ($statement->execute($data)) {
            return $this->getById($this->pdo->lastInsertId());
        } else {
            throw new Exception($this->pdo->errorInfo(), $this->pdo->errorCode());
        }
    }

    /**
     * @param string|integer $id
     * @param array<string,mixed> $data
     * @return PDOObject
     */
    public function updateById($id, array $data): PDOObject
    {
        assert($this->pdo);
        assert($this->table);

        $statement = $this->pdo->prepare("UPDATE {$this->table} SET " . array_map(function ($prop) {
            return "$prop = :$prop";
        }, array_keys($data)) . ' WHERE id = :id');
        if ($statement->execute(array_merge($data, ['id' => $id]))) {
            return $this->getById($id);
        } else {
            throw new Exception($this->pdo->errorInfo(), $this->pdo->errorCode());
        }
    }
}
