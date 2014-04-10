<?php

namespace R2\DBAL;

class PDOMySQL implements DBALInterface
{
    /** @var \PDO */
    private $link;
    /** @var \PDOStatement */
    private $result;
    private $dsn;
    private $persistent;
    private $username;
    private $password;
    private $prefix;
    private $log;

    /**
     * Constructor.
     * @param  array      $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'host'          => 'localhost',
            'username'      => 'root',
            'password'      => null,
            'dbname'        => null,
            'prefix'        => null,
            'socket'        => null,
            'log'           => null,
            'persistent'    => false
        ];
        $config = array_intersect_key($config, $defaults) + $defaults;
        // Keep data for lazy connection
        $this->persistent   = $config['persistent'];
        $this->username     = $config['username'];
        $this->password     = $config['password'];
        $dsn = 'mysql:';
        if (isset($config['socket'])) {
            $dsn .= 'unix_socket=' . $config['socket'] . ';';
        } else {
            if (strpos($config['host'], ':') !== false) {
                list($config['host'], $port) = explode(':', $config['host']);
            }
            $dsn .= 'host=' . $config['host'] . ';';
            if (isset($port)) {
                $dsn .= 'port=' . $port . ';';
            }
        }
        if (isset($config['dbname'])) {
            $dsn .= 'dbname=' . $config['dbname'] . ';';
        }
        $this->dsn = $dsn.'charset=UTF8;';
        // for query
        $this->prefix = $config['prefix'];
        // misc
        $this->log = $config['log'];
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (isset($this->link)) {
            $this->commit();
            $this->close();
        }
    }

    /**
     * (Lazy) connect.
     * @throws \Exception
     */
    private function connect()
    {
        $this->link = new \PDO($this->dsn, $this->username, $this->password, [
            \PDO::ATTR_PERSISTENT               => $this->persistent,
            \PDO::ATTR_ERRMODE                  => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_AUTOCOMMIT               => false,
            \PDO::ATTR_EMULATE_PREPARES         => true,
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]);
        $this->beginTransaction();
    }

    /**
     * Execute DB query.
     * @param  string            $sql         query text
     * @param  array             $queryParams named parameters, like [':name' => $value]
     * @return \R2\DBAL\PDOMySQL This object
     * @throws \Exception
     */
    public function query($sql, array $queryParams = [])
    {
        if (!isset($this->link)) {
            $this->connect();
        }
        $sql = str_replace(':p_', $this->prefix, $sql);
        //...
        $this->result = $this->link->prepare($sql);
        $this->result->execute();

        return $this;
    }

    /**
     * Begin transaction.
     */
    public function beginTransaction()
    {
        ++$this->inTransaction;
        if (isset($this->link)) {
            $this->link->beginTransaction();
        }
    }

    /**
     * Commit.
     */
    public function commit()
    {
        if (isset($this->link) && $this->link->inTransaction()) {
            $this->link->commit();
        }
    }

    /**
     * Rollback.
     */
    public function rollback()
    {
        if (isset($this->link) && $this->link->inTransaction()) {
            $this->link->rollBack();
        }
    }

    /**
     * Fetches single value.
     * @param  int          $row
     * @param  int          $col
     * @return string|false
     */
    public function result($row = 0, $col = 0)
    {
        $result = false;
        if (isset($this->result)) {
            do {
                $data = $this->result->fetch(\PDO::FETCH_NUM);
            } while ($row-- > 0);
            $result = $data[$col];
        }

        return $result;
    }

    /**
     * Gets a result row as an associative array.
     * @return array|false
     */
    public function fetchAssoc()
    {
        return isset($this->result) ? $this->result->fetch(\PDO::FETCH_ASSOC) : false;
    }

    /**
     * Gets result rows where each row is an associative array.
     * @return array|false
     */
    public function fetchAssocAll()
    {
        return isset($this->result) ? $this->result->fetchAll(\PDO::FETCH_ASSOC) : false;
    }

    /**
     * Gets a result row as an enumerated array.
     * @return array|false
     */
    public function fetchRow()
    {
        return isset($this->result) ? $this->result->fetch(\PDO::FETCH_NUM) : false;
    }

    /**
     * Gets the number of rows in a result.
     * @return int|false
     */
    public function numRows()
    {
        // WARNING: this behaviour is not guaranteed for all db, but for MySQL it works
        return isset($this->result) ? $this->result->rowCount() : false;
    }

    /**
     * Gets the number of affected rows in a previous operation.
     * @return int|false
     */
    public function affectedRows()
    {
        return isset($this->result) ? $this->result->rowCount() : false;
    }

    /**
     * Returns the auto generated id used in the last query.
     * @return int|false
     */
    public function insertId()
    {
        // WARNING: may not return a meaningful result for all db, but for MySQL it works
        return $this->link ? $this->link->lastInsertId() : false;
    }

    /**
     * Frees the memory associated with a result.
     * @return \R2\DBAL\PDOMySQL This object
     */
    public function freeResult()
    {
        if (isset($this->result)) {
            $this->result->closeCursor();
            unset($this->result);
        }

        return $this;
    }

    /**
     * Closes DB connection.
     * @return R2\DBAL\PDOMySQL This object
     */
    public function close()
    {
        if (isset($this->link)) {
            unset($this->result);
            unset($this->link);
        }

        return $this;
    }
}
