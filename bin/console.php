#!/usr/bin/env php
<?php

require __DIR__.'/../src/bootstrap.php';

// Is it command line?
if (PHP_SAPI === 'cli') {
    $shortOpts = 'e:';
    $longOpts = ['env:'];
    $defaults = [
        'env' => 'production',
    ];
    // Drop wrong keys and set defaults
    $options = array_intersect_key(getopt($shortOpts, $longOpts), $defaults) + $defaults;
    // All except keys, are command and its arguments
    $arguments = array_filter($argv, function ($x) { return $x{0} != '-'; });
    array_shift($arguments);            // argv[0] is a script name itself
    $command = array_shift($arguments); // argv[1] is a command, all the rest is command arguments
} else {
    die('*** This script is for CLI only!');
}

//echo 'env: '.var_export($options['env'], true)."\n";
//echo 'command: '.var_export($command, true)."\n";
//echo 'arguments: '.var_export($arguments, true)."\n";
//echo "----------------------------\n\n";

switch ($command) {
case 'dbal:schema:create': case 'dbal:schema:update':
    dropSchema($options, $arguments);
    createSchema($options, $arguments);
    exit(0);
case 'dbal:schema:drop':
    dropSchema($options, $arguments);
    exit(0);
}

die("Unicycle console util\n"
  . "\n"
  . "Usage:\n"
  . "  [options] command [arguments]\n"
  . "\n"
  . "Options:\n"
  . "  --env  The environment name. Default: production\n"
  . "\n"
  . "Available commands:\n"
  . "dbal\n"
  . "  dbal:schema:create     The same as dbal:schema:update\n"
  . "  dbal:schema:drop       Drop all tables in schema\n"
  . "  dbal:schema:update     Drop all tables than create new from predefined sql\n"
);

function loadParams($environment)
{
    $loader = new \R2\Config\YamlFileLoader();
    $resource = __DIR__."/../app/config/parameters/{$environment}.yml";
    try {
        $data = $loader->load($resource)['parameters'];
    } catch (\Exception $ex) {
        die("*** Cannot load parameters for environment \"{$environment}\"");
    }

    return $data;
}

function dropSchema($options, $arguments)
{
    $environment = $options['env'];
    $dbParams = loadParams($environment)['db_params'];
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
        die("Database error:\n".$ex->getMessage());
    }

    echo "Schema dropped\n";
}

function createSchema($options, $arguments)
{
    $environment = $options['env'];
    $dbParams = loadParams($environment)['db_params'];
    try {
        $dbh = new \R2\DBAL\PDOMySQL($dbParams);

        $schema = file_get_contents(__DIR__.'/../app/install/create-schema.sql');
        $schema = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($schema as $sql) {
            $dbh->query($sql);
        }

        $data = file_get_contents(__DIR__.'/../app/install/db-fixtures.sql');
        $data = array_filter(array_map('trim', explode(';', $data)));
        foreach ($data as $sql) {
            $dbh->query($sql);
        }

        $dbh->commit();
    } catch (\Exception $ex) {
        die("Database error:\n".$ex->getMessage());
    }

    echo "Schema created\n";
}
