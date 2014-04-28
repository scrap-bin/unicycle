<?php

namespace R2\Application\Command;

use R2\Application\CliApplication;

class DBALCommand extends CliApplication
{
    public function dropSchema()
    {
        try {
            $prefix = $this->db->getPrefix();
            foreach ($this->db->query("SHOW FULL TABLES")->fetchAssocAll() as $row) {
                $tableName = current($row);
                $tableType = next($row);
                if (stripos($tableName, $prefix) === 0 && in_array($tableType, ['BASE TABLE', 'VIEW'])) {
                    if ($tableType == 'BASE TABLE') {
                        $this->db->query("DROP TABLE `{$tableName}`");
                    } elseif ($tableType == 'VIEW') {
                        $this->db->query("DROP VIEW `{$tableName}`");
                    }
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
            $dir = $this->container->getParameter('parameters.root_dir').'/install';
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
            $dir = $this->container->getParameter('parameters.root_dir').'/install';
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
