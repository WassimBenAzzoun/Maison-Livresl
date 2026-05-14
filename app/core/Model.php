<?php

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function execute(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll();
    }

    protected function hydrateRows(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->hydrateRow($row);
        }

        return $items;
    }

    abstract protected function hydrateRow(array $row): object;

    protected function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->execute($sql, $params)->fetch();
    }
}
