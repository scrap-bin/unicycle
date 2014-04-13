<?php

namespace R2\ORM;

class EntityRepository implements EntityRepositoryInterface
{
    /** @var R2\ORM\EntityManagerInterface */
    protected $entityManager;
    protected $entityClass;
    protected $db;
    protected $meta;

    public function __construct(EntityManagerInterface $entityManager, $entityClass)
    {
        $this->entityManager = $entityManager;
        $this->entityClass   = $entityClass;
        $this->db            = $entityManager->getConnection();
    }

    protected function getMeta()
    {
        if (!isset($this->meta)) {
            $this->meta = $this->entityManager->getMeta($this->entityClass);
        }

        return $this->meta;
    }

    /**
     * Get Entity Manager
     * @return R2\ORM\EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Finds an object by its primary key / identifier.
     * @param  mixed  $id The identifier.
     * @return object The entity object.
     */
    public function find($id)
    {
        $meta = $this->getMeta();
        $table = $meta['table'];
        $where = implode(
            ' AND ',
            array_map(
                function ($x) {
                    return "`{$x}`=:{$x}";
                },
                $meta['key']
            )
        );
        $sql = "SELECT * FROM `:p_{$table}` WHERE {$where}";
        $params = array_combine(
            $meta['key'],
            (array) $id
        );

        $db = $this->db->query($sql, $params);
        if (!$db->numRows()) {
            throw new \InvalidArgumentException('Empty result');
        }
        $row = $db->fetchAssoc();
        $entityClass = $this->entityClass;

        return $this->load(new $entityClass(), $row);
    }

    /**
     * Finds all objects in the repository.
     * @return EntityIterator The list.
     */
    public function findAll()
    {
        $meta = $this->getMeta();
        $table = $meta['table'];
        $sql = "SELECT * FROM `:p_{$table}`";
        $rows = $this->db->query($sql)->fetchAssocAll();

        return new EntityIterator($this, $rows);
    }

    /**
     * Finds objects by a set of criteria.
     * @param  array          $criteria
     * @param  array|null     $orderBy
     * @param  int|null       $limit
     * @param  int|null       $offset
     * @return EntityIterator The list
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $rows = $this->buildQuery($criteria, $orderBy, $limit, $offset)->fetchAssocAll();

        return new EntityIterator($this, $rows);
    }

    /**
     * Finds a single object by a set of criteria.
     * @param  array  $criteria The criteria.
     * @return object The entity object.
     */
    public function findOneBy(array $criteria)
    {
        $db = $this->buildQuery($criteria, null, 1, null);
        if (!$db->numRows()) {
            throw new \InvalidArgumentException('Empty result');
        }
        $row = $db->fetchAssoc();
        $entityClass = $this->entityClass;

        return $this->load(new $entityClass(), $row);
    }

    /**
     * Build and run SQL query.
     * @param  array                 $criteria
     * @param  array|null            $orderBy
     * @param  int|null              $limit
     * @param  int|null              $offset
     * @return R2\Dbal\DbalInterface
     */
    private function buildQuery(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $meta = $this->getMeta();
        $table = $meta['table'];
        $sql = "SELECT * FROM `:p_{$table}`";
        $params = [];
        if (!empty($criteria)) {
            $tmp = [];
            foreach ($criteria as $field => $value) {
                $column = $meta['fields'][$field];
                $tmp[] = is_array($value)
                    ? "`{$column}` IN(:{$column})"
                    : "`{$column}`=:{$column}";
                $params[$column] = $value;
            }
            $sql .= ' WHERE '.implode(' AND ', $tmp);
        }
        if (!empty($orderBy)) {
            $tmp = [];
            foreach ($orderBy as $field => $order) {
                $column = $meta['fields'][$field];
                $tmp[] = $column.' '.$order;
            }
            $sql .= ' ORDER BY '.implode(', ', $tmp);
        }
        if ($limit > 0) {
            if ($offset > 0) {
                $sql .= ' LIMIT '.$offset.', '.$limit;
            } else {
                $sql .= ' LIMIT '.$limit;
            }
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->entityClass;
    }

    /**
     * Make an instance persistent.
     * @param  object           $entity
     * @return EntityRepository
     * @throws \DomainException
     */
    public function persist($entity)
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new \DomainException('Wrong entity class');
        }
        $meta = $this->getMeta();
        $tableName = $meta['table'];
        $keys = $meta['key'];
        $generator = $meta['generator'];
        $columnsMinusKey = $columns = $meta['fields'];
        // Some magic
        $isNew = true;
        foreach ($keys as $c) {
            $field = array_search($c, $meta['fields']);
            $isNew = $isNew && empty($entity->$field);
            unset($columnsMinusKey[$c]);
        }
        if ($generator != 'autoInc') {
            $this->$generator($entity);
        }
        $vars = [];
        foreach ($columns as $f => $c) {
            $vars[$c] = $entity->$f;
        }
        if ($isNew) {
            if ($generator == 'autoInc') {
                $columns = $columnsMinusKey;
            }
            $names  = implode(
                ',',
                array_map(
                    function ($x) {
                        return "`{$x}`";
                    },
                    $columns
                )
            );
            $values = implode(
                ',',
                array_map(
                    function ($x) {
                        return ":{$x}";
                    },
                    $columns
                )
            );
            $this->db->query("INSERT INTO `:p_{$tableName}`({$names})\nVALUES({$values})", $vars);
            if ($generator == 'autoInc') {
                $entity->{$keys[0]} = $this->db->insertId();
            }
        } else {
            $set = implode(
                ",\n",
                array_map(
                    function ($x) {
                        return "`{$x}`=:{$x}";
                    },
                    $columnsMinusKey
                )
            );
            $where = implode(
                ' AND ',
                array_map(
                    function ($x) {
                        return "`{$x}`=:{$x}";
                    },
                    $keys
                )
            );
            $this->db->query("UPDATE `:p_{$tableName}`SET\n{$set}\nWHERE {$where}", $vars);
        }

        return $this;
    }

    public function remove($entity)
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new \DomainException('Wrong entity class');
        }
        $meta = $this->getMeta();
        $tableName = $meta['table'];
        $keys = $meta['key'];
        // Some magic
        $isNew = true;
        $vars = $where = [];
        foreach ($keys as $c) {
            $field = array_search($c, $meta['fields']);
            $isNew = $isNew && empty($entity->$field);
            $vars[$c] = $entity->$field;
            $where[] = "`{$c}`=:{$c}";
        }
        if (!$isNew) {
            $where = implode(' AND ', $where);
            $this->db->query("DELETE FROM `:p_{$tableName}` WHERE {$where}", $vars);
        }

        return $this;
    }

    public function refresh($entity)
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new \DomainException('Wrong entity class');
        }
        $meta = $this->getMeta();
        $tableName = $meta['table'];
        $keys = $meta['key'];
        // Some magic
        $isNew = true;
        $vars = $where = [];
        foreach ($keys as $c) {
            $field = array_search($c, $meta['fields']);
            $isNew = $isNew && empty($entity->$field);
            $vars[$c] = $entity->$field;
            $where[] = "`{$c}`=:{$c}";
        }
        if (!$isNew) {
            $where = implode(' AND ', $where);
            $row = $this->db->query("SELECT * FROM `:p_{$tableName}` WHERE {$where}", $vars);
            $this->load($entity, $row);
        }

        return $this;
    }

    /**
     * Set entity fields from array.
     * Note: array can cover all fields or some subset only.
     * @param  object $entity
     * @param  array  $init
     * @return object
     */
    public function load($entity, array $init)
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new \DomainException('Wrong entity class');
        }
        if (!empty($init)) {
            foreach ($this->getMeta()['fields'] as $field => $column) {
                if (array_key_exists($column, $init)) {
                    $entity->$field = $init[$column];
                }
            }
        }

        return $entity;
    }

    /**
     * Return entity fields as array.
     * Note: you can use optional fieldNames list to unload subset only.
     * @param  object $entity
     * @param  array  $fieldNames
     * @return array
     */
    public function unload($entity, array $fieldNames = [])
    {
        if (!is_a($entity, $this->entityClass)) {
            throw new \DomainException('Wrong entity class');
        }
        $array = [];
        foreach ($this->getMeta()['fields'] as $field => $column) {
            if (empty($fieldNames) || in_array($field, $fieldNames)) {
                $array[$column] = $entity->$field;
            }
        }

        return $array;
    }

    protected function generateKey($entity)
    {
        throw new \InvalidArgumentException('Key generator not overriden');
    }

    /**
     * Gets field name by column name.
     *
     * @param string $column Column name
     *
     * @return string
     */
    public function getFieldByColumn($column)
    {
        return array_search($column, $this->getMeta()['fields']);
    }

    /**
     * Gets column name by field name.
     *
     * @param string $field Field name
     *
     * @return string
     * @throws BadMethodCallException
     */
    public function getColumnByField($field)
    {
        return $this->getMeta()['fields'][$field];
    }
}
