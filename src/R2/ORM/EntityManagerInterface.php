<?php

namespace R2\ORM;

interface EntityManagerInterface
{
    public function getConnection();
    public function getRepository($entity);
    public function beginTransaction();
    public function commit();
    public function rollback();
    public function getMeta($entity);
    public function nativeQuery($sql, array $queryParams = []);
    public function get($entity);
    public function getAll($entity);
}
