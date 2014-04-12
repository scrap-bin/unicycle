<?php

namespace R2\ORM;

use R2\Dbal\DbalInterface;
use InvalidArgumentException as ArgsException;

/**
 * The EntityManager is the central access point to Data Mapper functionality.
 */
class EntityManager implements EntityManagerInterface
{
    protected $db;
    protected $config;

    const DEFAULT_REPOSITORY_CLASS = 'R2\\ORM\\EntityRepository';
    /**
     * Constructor
     * @param DbalInterface $db
     * @param array         $config
     */
    public function __construct(DbalInterface $conn, array $config = [])
    {
        $this->db     = $conn;
        $this->config = $config;
    }

    /**
     * Gets the database connection object used by the EntityManager.
     * @return DbalInterface
     */
    public function getConnection()
    {
        return $this->db;
    }

    /**
     * Find repository for given entity
     * @param  mixed            $entity Enity instance or class name
     * @return EntityRepository
     * @throws ArgsException
     */
    public function getRepository($entity)
    {
        return $this->getMeta($entity)['repository'];
    }

    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollback()
    {
        $this->db->rollback();
    }

    public function getMeta($entity)
    {
        $entityClass = $this->getEntityClass($entity);

        if (!isset($this->config[$entityClass])) {
            for ($c = $entityClass; $c; $c = get_parent_class($c)) {
                if (array_key_exists($c, $this->config)) {
                    return $this->config[$c];
                }
                $repoClass = $c.'Repository';
                if (class_exists($repoClass, true)) {
                    break;
                }
            }
            if (!$c) {
                $repoClass = self::DEFAULT_REPOSITORY_CLASS;
            }
            // We are so naive!
            $fields = $this->getFields($entityClass);
            $key = [];
            $field = reset($fields);
            if ($field == 'id') {
                $key = ['id'];
            } else {
                while (substr($field, -3) == '_id' || substr($field, 0, 2) == 'id') {
                    $key[] = $field;
                    $field = next($fields);
                }
            }
            if (empty($key)) {
                $key[] = reset($fields);
            }
            $table = $entityClass;
            if (false !== $p = strrpos($table, '\\')) {
                $table = substr($table, $p + 1);
            }
            $table = strtolower(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $table)).'s';
            $this->config[$entityClass] = [
                'repository' => $repoClass,
                'table'      => $table,
                'fields'     => $fields,
                'key'        => $key,
                'generator'  => ($key == ['id'] ? 'autoInc' : 'generateKey'),
            ];
        }

        if (!is_object($this->config[$entityClass]['repository'])) {
            $class = $this->config[$entityClass]['repository'];
            $this->config[$entityClass]['repository'] = new $class($this, $entityClass);
        }

        return $this->config[$entityClass];
    }

    private function getEntityClass($entity)
    {
        if (is_string($entity) && class_exists($entity)) {
            return $entity;
        } elseif (is_object($entity)) {
            return get_class($entity);
        } else {
            throw new ArgsException('Wrong entity for which you need to find repository.');
        }
    }

    private function getFields($entityClass)
    {
        $fields = [];
        $vars = get_class_vars($entityClass);
        foreach (array_keys($vars) as $field) {
            if ($field{0} != '_') {
                $column = strtolower(preg_replace('/([^A-Z])([A-Z])/', '$1_$2', $field));
                $fields[$field] = $column;
            }
        }

        return $fields;
    }

    public function getFieldByColumn($entity, $column)
    {
        $meta = $this->getMeta($entity);

        return array_search($column, $meta['fields']);
    }

    public function getColumnByField($entity, $field)
    {
        $meta = $this->getMeta($entity);

        return $meta['fields'][$field];
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, ['persist', 'remove', 'refresh', 'load', 'unload'])) {
            $entity = $arguments[0];
        } elseif (0 === strpos($method, 'find')) {
            $entity = array_shift($arguments);
        }
        if (isset($entity)) {
            $repository = $this->getRepository($entity);
            if (method_exists($repository, $method)) {
                switch (count($arguments)) {
                    case 0:
                        return $repository->$method();
                    case 1:
                        return $repository->$method($arguments[0]);
                    case 2:
                        return $repository->$method($arguments[0], $arguments[1]);
                    case 3:
                        return $repository->$method($arguments[0], $arguments[1], $arguments[2]);
                    default:
                        return call_user_func_array([$repository, $method], $arguments);
                }
            }
        }
        throw new \BadMethodCallException("Undefined method '{$method}'.");
    }

    public function nativeQuery($sql, array $queryParams = [])
    {
        $this->db->query($sql, $queryParams);

        return $this;
    }

    public function get($entity)
    {
        if (!$this->db->numRows()) {
            throw new \InvalidArgumentException('Empty result');
        }
        $entityClass = $this->getEntityClass($entity);

        return $this->getRepository($entityClass)
            ->load(new $entityClass(), $this->db->fetchAssoc());
    }

    public function getAll($entity)
    {
        return new EntityIterator($this->getRepository($entity), $this->db->fetchAssocAll());
    }
}
