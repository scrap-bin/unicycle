<?php

namespace R2\Command;

class DBALCommand
{
    public static function loadParams($environment)
    {
        $loader = new \R2\Config\YamlFileLoader();
        $resource = __DIR__."/../../../app/config/parameters/{$environment}.yml";
        try {
            $data = $loader->load($resource)['parameters'];
        } catch (\Exception $ex) {
            fwrite(STDERR, "*** Cannot load parameters for environment \"{$environment}\"\n");
            exit(1);
        }

        return $data;
    }

    public static function dropSchema($options, $arguments)
    {
        $environment = $options['env'];
        $dbParams = self::loadParams($environment)['db_params'];
        try {
            $dbh = new \R2\DBAL\PDOMySQL($dbParams);
            foreach ($dbh->query("SHOW TABLES")->fetchAssocAll() as $row) {
                $tableName = current($row);
                if (stripos($tableName, $dbParams['prefix']) === 0) {
                    $dbh->query("DROP TABLE `{$tableName}`");
                }
            }
            $dbh->commit();
        } catch (\Exception $ex) {
            fwrite(STDERR, "*** Database error:\n".$ex->getMessage()."\n");
            exit(1);
        }

        echo "Schema dropped\n";
    }

    public static function createSchema($options, $arguments)
    {
        $environment = $options['env'];
        $dbParams = self::loadParams($environment)['db_params'];
        try {
            $dbh = new \R2\DBAL\PDOMySQL($dbParams);

            $schema = file_get_contents(__DIR__.'/../../../app/install/create-schema.sql');
            $schema = array_filter(array_map('trim', explode(';', $schema)));
            foreach ($schema as $sql) {
                $dbh->query($sql);
            }

            $data = file_get_contents(__DIR__.'/../../../app/install/db-fixtures.sql');
            $data = array_filter(array_map('trim', explode(';', $data)));
            foreach ($data as $sql) {
                $dbh->query($sql);
            }

            $dbh->commit();
        } catch (\Exception $ex) {
            fwrite(STDERR, "*** Database error:\n".$ex->getMessage()."\n");
            exit(1);
        }

        echo "Schema created\n";
    }
}
