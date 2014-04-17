#!/usr/bin/env php
<?php

use R2\Application\Command\DBALCommand;
use R2\Application\Container;
use R2\Config\YamlFileLoader;

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
    $arguments = array_filter(
        $argv,
        function ($x) {
            return $x{0} != '-';
        }
    );
    array_shift($arguments);            // argv[0] is a script name itself
    $command = array_shift($arguments); // argv[1] is a command, all the rest is command arguments
} else {
    exit('*** This script is for CLI only!');
}

$config = __DIR__.'/../app/config/config.yml';
$container = new Container(new YamlFileLoader(), $config, $options['env']);

switch ($command) {
    case 'dbal:schema:create':
    case 'dbal:schema:update':
        (new DBALCommand($options, $arguments))
            ->setContainer($container)
            ->dropSchema()
            ->createSchema();
        exit(0);
    case 'dbal:schema:drop':
        (new DBALCommand($options, $arguments))
            ->setContainer($container)
            ->dropSchema();
        exit(0);
    case 'dbal:fixtures:load':
        (new DBALCommand($options, $arguments))
            ->setContainer($container)
            ->loadFixtures();
        exit(0);
}

?>
Unicycle console utility

Usage:
  console [options] command [arguments]

Options:
  --env  The environment name. Default: production

Available commands:
dbal
  dbal:schema:create     The same as dbal:schema:update
  dbal:schema:drop       Drop all tables in schema
  dbal:schema:update     Drop all tables than create new from predefined sql
  dbal:fixtures:load     Load playground data
<?php
exit(0);
