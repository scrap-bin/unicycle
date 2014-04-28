<?php

namespace R2\DBAL;

use Exception;

/**
 * DataBase Abstraction Level. Interface to PHP mysqli extension.
 */
class Mysqli implements DBALInterface
{
    /** @var \Mysqli */
    private $link;
    /** @var \Mysqli_Result */
    private $result;
    private $host;
    private $dbname;
    private $inTransaction;
    private $persistent;
    private $username;
    private $password;
    private $prefix;
    private $log;

    /**
     * Constructor.
     *
     * @param array $config Configuration parameters
     */
    public function __construct(array $config = [])
    {
        // Filter config keys and apply default values
        $defaults = [
            'host'         => 'localhost',
            'username'     => 'root',
            'password'     => '',
            'dbname'       => '',
            'prefix'       => '',
            'socket'       => null,
            'log'           => null,
            'persistent'   => false
        ];
        $config = array_intersect_key($config, $defaults) + $defaults;
        // for lazy connection
        $this->host = $config['host'];
        $this->persistent = $config['persistent'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->dbname = $config['dbname'];
        // for query
        $this->prefix = $config['prefix'];
        // misc
        $this->log = $config['log'];
        $this->inTransaction = 0;
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
     * Gets table prefix.
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * (Lazy) connect.
     *
     * @throws Exception
     */
    private function connect()
    {
        // Was a custom port supplied with host?
        $port = null;
        if (strpos($this->host, ':') !== false) {
            list($this->host, $port) = explode(':', $this->host);
        }
        $this->link = mysqli_connect(
            ($this->persistent ? 'p:' : '').$this->host,
            $this->username,
            $this->password,
            $this->dbname,
            $port
        );
        if (!$this->link) {
            unset($this->link);
            throw new Exception('Unable to connect database');
        }
        mysqli_autocommit($this->link, false);   // in general, we need one commit over page
        mysqli_set_charset($this->link, 'utf8'); // must have to correct mysqli_real_escape_string!
        $this->beginTransaction();
    }

    private $paramsIn;

    /**
     * Callback to bind named parameters
     *
     * @param type $matches preg_matched values
     *
     * @return string
     */
    private function replace($matches)
    {
        $var = $this->paramsIn[$matches[1]];
        if (is_string($var)) {
            return "'" . mysqli_real_escape_string($this->link, $var) . "'";
        } elseif (is_array($var)) {
            $tmp = [];
            foreach ($var as $item) {
                if (is_string($item)) {
                    $tmp[] = "'" . mysqli_real_escape_string($this->link, $item) . "'";
                } elseif (is_null($item)) {
                    $tmp[] = 'null';
                } else {
                    $tmp[] = $item;
                }
            }
            $var = implode(',', $tmp);
        } elseif (is_null($var)) {
            return 'null';
        }

        return $var;
    }

    /**
     * Execute DB query.
     * Provides a fluent interface.
     *
     * @param string $sql         Query text
     * @param array  $queryParams Named parameters, like ['name' => $value]
     *
     * @return Mysqli
     * @throws Exception
     */
    public function query($sql, array $queryParams = [])
    {
        // Lazy connection
        if (!isset($this->link)) {
            $this->connect();
        }
        $this->paramsIn = $queryParams;
        if (strpos($sql, ':') !== false) {
            // Special case - table prefix
            $sql = str_replace(':p_', $this->prefix, $sql);
            // Find placeholders
            if (strpos($sql, ':') !== false) {
                // Skip string literals
                $pattern =
                    '/(?:'
                    .   "'[^'\\\\]*(?:(?:\\\\.|'')[^'\\\\]*)*'"
                    .  '|"[^"\\\\]*(?:(?:\\\\.|"")[^"\\\\]*)*"'
                    .  '|`[^`\\\\]*(?:(?:\\\\.|``)[^`\\\\]*)*`'
                    .')(*SKIP)(*F)'
                    .'|(?:\:)([a-zA-Z][a-zA-Z0-9_]*)/';
                // Custom placeholders
                $sql = preg_replace_callback($pattern, [$this, 'replace'], $sql);
            }
        }
        $this->result = mysqli_query($this->link, $sql);
        if ($this->result) {
            return $this;
        } else {
            $this->rollback();
            throw new Exception("Error in query\n".mysqli_error($this->link)."\n".$sql);
        }
    }

    /**
     * Begin transaction.
     * Provides a fluent interface.
     *
     * @return Mysqli
     */
    public function beginTransaction()
    {
        ++$this->inTransaction;
        if (isset($this->link)) {
            // is it necessary after mysqli_autocommit($this->link, false) ?
            mysqli_query($this->link, "START TRANSACTION");
        }

        return $this;
    }

    /**
     * Commit.
     * Provides a fluent interface.
     *
     * @return Mysqli
     */
    public function commit()
    {
        --$this->inTransaction;
        if (isset($this->link)) {
            mysqli_commit($this->link);
        }

        return $this;
    }

    /**
     * Rollback.
     * Provides a fluent interface.
     *
     * @return Mysqli
     */
    public function rollback()
    {
        $this->inTransaction = 0;
        if (isset($this->link)) {
            mysqli_rollback($this->link);
        }

        return $this;
    }

    /**
     * Fetches single value.
     *
     * @param int $row
     * @param int $col
     *
     * @return string|false
     */
    public function result($row = 0, $col = 0)
    {
        if (isset($this->result)) {
            if ($row == 0 || mysqli_data_seek($this->result, $row) !== false) {
                if (($cur_row = mysqli_fetch_row($this->result)) !== false) {
                    return $cur_row[$col];
                }
            }
        }

        return false;
    }

    /**
     * Gets a result row as an associative array.
     *
     * @return array|false
     */
    public function fetchAssoc()
    {
        return isset($this->result) ? mysqli_fetch_assoc($this->result) : false;
    }

    /**
     * Gets result rows where each row is an associative array.
     *
     * @return array|false
     */
    public function fetchAssocAll()
    {
        $rows = false;
        if (isset($this->result)) {
            $rows = [];
            while ($row = mysqli_fetch_assoc($this->result)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Gets a result row as an enumerated array.
     *
     * @return array|false
     */
    public function fetchRow()
    {
        return isset($this->result) ? mysqli_fetch_row($this->result) : false;
    }

    /**
     * Gets the number of rows in a result.
     *
     * @return int|false
     */
    public function numRows()
    {
        return isset($this->result) ? mysqli_num_rows($this->result) : false;
    }

    /**
     * Gets the number of affected rows in a previous operation.
     *
     * @return int|false
     */
    public function affectedRows()
    {
        return isset($this->link) ? mysqli_affected_rows($this->link) : false;
    }

    /**
     * Returns the auto generated id used in the last query.
     *
     * @return int (or false)
     */
    public function insertId()
    {
        return isset($this->link) ? mysqli_insert_id($this->link) : false;
    }

    /**
     * Frees the memory associated with a result.
     * Provides a fluent interface.
     *
     * @return Mysqli
     */
    public function freeResult()
    {
        if (isset($this->result) && $this->result instanceof \mysqli_result) {
            mysqli_free_result($this->result);
            unset($this->result);
        }

        return $this;
    }

    /**
     * Closes DB connection.
     * Provides a fluent interface.
     *
     * @return Mysqli
     */
    public function close()
    {
        if (isset($this->link)) {
            $this->freeResult();
            mysqli_close($this->link);
        }
        unset($this->link);
        $this->inTransaction = 0;

        return $this;
    }
}
