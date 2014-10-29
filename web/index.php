<?php

use R2\Application\Container;
use R2\Config\CachedFileLoader;
use R2\Config\YamlFileLoader;
use R2\Application\WebApplication;

require __DIR__.'/../src/bootstrap.php';

$container = new Container(
    new CachedFileLoader(new YamlFileLoader(), __DIR__.'/../app/cache/%s.ser'),
    __DIR__.'/../app/config/config.yml',
    filter_input(INPUT_SERVER, 'APPLICATION_ENV') ?: 'production'
);
(new WebApplication())
    ->setContainer($container)
    ->handleRequest();
