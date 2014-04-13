<?php

namespace R2\ORM;

interface EntityRepositoryInterface
{
    public function find($id);
    public function findAll();
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
    public function findOneBy(array $criteria);
    public function getClassName();
    public function persist($entity);
    public function remove($entity);
    public function refresh($entity);
    public function load($entity, array $init);
    public function unload($entity, array $fieldNames = []);
    public function getFieldByColumn($column);
    public function getColumnByField($field);
}
