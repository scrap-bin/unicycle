<?php

namespace R2\DBAL;

interface DBALInterface
{
    /**
     * Execute DB query.
     * @param  string     $sql         Query text
     * @param  array      $queryParams Named parameters, like [':name' => $value]
     * @return $this      This object
     * @throws \Exception
     */
    public function query($sql, array $queryParams = []);
    /**
     * Begin transaction.
     */
    public function beginTransaction();
    /**
     * Commit.
     */
    public function commit();
    /**
     * Rollback.
     */
    public function rollback();
    /**
     * Fetches single value.
     * @param  int          $row
     * @param  int          $col
     * @return string|false
     */
    public function result($row = 0, $col = 0);
    /**
     * Gets a result row as an associative array.
     * @return array|false
     */
    public function fetchAssoc();
    /**
     * Gets result rows where each row is an associative array.
     * @return array|false
     */
    public function fetchAssocAll();
    /**
     * Gets a result row as an enumerated array.
     * @return array|false
     */
    public function fetchRow();
    /**
     * Gets the number of rows in a result.
     * @return int|false
     */
    public function numRows();
    /**
     * Gets the number of affected rows in a previous operation.
     * @return int|false
     */
    public function affectedRows();
    /**
     * Returns the auto generated id used in the last query.
     * @return int|false
     */
    public function insertId();
    /**
     * Frees the memory associated with a result.
     * @return \R2\DBAL\PDOMySQL This object
     */
    public function freeResult();
    /**
     * Closes DB connection.
     * @return R2\DBAL\PDOMySQL This object
     */
    public function close();
}
