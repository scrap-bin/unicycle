<?php

namespace R2\Application\Command;

use R2\Application\CliApp;

class DBALCommand extends CliApp
{
    public function dropSchema()
    {
        try {
            $prefix = $this->container->getParameter('parameters.db_params.prefix');
            foreach ($this->db->query("SHOW TABLES")->fetchAssocAll() as $row) {
                $tableName = current($row);
                if (stripos($tableName, $prefix) === 0) {
                    $this->db->query("DROP TABLE `{$tableName}`");
                }
            }
            $this->db->commit()->beginTransaction();
        } catch (\Exception $ex) {
            fwrite(STDERR, "*** Database error:\n".$ex->getMessage()."\n");
            exit(1);
        }
        fwrite(STDOUT, "Schema dropped\n");

        return $this;
    }

    public function createSchema()
    {
        try {
            $dir = $this->container->getParameter('parameters.root_dir').'/../install';
            $schema = array_filter(
                array_map(
                    'trim',
                    explode(';', file_get_contents($dir.'/create-schema.sql'))
                )
            );
            foreach ($schema as $sql) {
                $this->db->query($sql);
            }
            $this->db->commit()->beginTransaction();
        } catch (\Exception $ex) {
            fwrite(STDERR, "*** Database error:\n".$ex->getMessage()."\n");
            exit(1);
        }
        fwrite(STDOUT, "Schema created\n");

        return $this;
    }

    public function loadFixtures()
    {
        try {
            $dir = $this->container->getParameter('parameters.root_dir').'/../install';
            $data = array_filter(
                array_map(
                    'trim',
                    explode(';', file_get_contents($dir.'/db-fixtures.sql'))
                )
            );
            foreach ($data as $sql) {
                $this->db->query($sql);
            }

            $this->db->commit()->beginTransaction();
        } catch (\Exception $ex) {
            fwrite(STDERR, "*** Database error:\n".$ex->getMessage()."\n");
            exit(1);
        }
        fwrite(STDOUT, "Fixtures loaded\n");

        return $this;
    }
}