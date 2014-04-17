<?php

namespace R2\ORM;

use R2\ORM\EntityRepositoryInterface as RepoInterface;

class EntityIterator extends \ArrayIterator
{
    private $repo;
    private $entityInstance;

    /**
     * Constructor
     * @param RepoInterface $repo
     * @param array         $records
     */
    public function __construct(RepoInterface $repo, array $records)
    {
        $this->repo = $repo;
        parent::__construct($records);
    }

    public function current()
    {
        return $this->repo->load($this->getEntityInstance(), parent::current());
    }

    public function offsetGet($index)
    {
        return $this->repo->load($this->getEntityInstance(), parent::offsetGet($index));
    }

    /**
     * Get all unique values of given field.
     * Note: this method didn't instantiate entity.
     * @param  string $field
     * @return array
     */
    public function getAllFieldValues($field)
    {
        $column = $this->repo->getColumnByField($field);
        $result = [];
        for ($this->rewind(); $this->valid(); $this->next()) {
            $result[] = parent::current()[$column];
        }

        return array_unique($result);
    }

    protected function getEntityInstance()
    {
        if (!isset($this->entityInstance)) {
            $entityClass = $this->repo->getClassName();
            $this->entityInstance = new $entityClass();
        }

        return $this->entityInstance;
    }
}
