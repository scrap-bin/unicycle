<?php

namespace R2\DBAL;

interface DBALInterface
{
    public function getPrefix();
    public function query($sql, array $queryParams = []);
    public function beginTransaction();
    public function commit();
    public function rollback();
    public function result($row = 0, $col = 0);
    public function fetchAssoc();
    public function fetchAssocAll();
    public function fetchRow();
    public function numRows();
    public function affectedRows();
    public function insertId();
    public function freeResult();
    public function close();
}
