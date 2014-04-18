#!/usr/bin/env php
<?php

$db = array(
    'Mysqli'    => function_exists('mysqli_connect') ? 1 : 0,
    'PDOMysql'  => (class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers())) ? 1 : 0,
);

echo 'PHP version ...... '.(version_compare(PHP_VERSION, '5.4', '>=') ? 'OK' : 'ERROR')."\n";

echo 'JSON support ..... '.(function_exists('json_encode') ? 'OK' : 'ERROR')."\n";

echo 'YAML support ..... '.(function_exists('yaml_parse_file')
        ? 'OK'
        : (file_exists(__DIR__.'/../src/fallback/Yaml.php')
            ? 'WARNING. Workaround in action.'
            : 'ERROR'))."\n";

echo 'MySQL support(1) . '.(array_sum($db) ? 'OK' : 'ERROR')."\n";

echo 'Cache writable ... '.(is_writable(__DIR__.'/../app/cache') ? 'OK' : 'ERROR. Chmod +x to app/cache!')."\n";

echo 'Log writable ..... '.(is_writable(__DIR__.'/../app/logs') ? 'OK' : 'ERROR. Chmod +x to app/logs!')."\n";

echo 'Configuration(2) . '.(count(glob(__DIR__.'/../app/config/parameters/*.yml'))
        ? 'OK'
        : 'ERROR. Define at least one!')."\n";

echo <<<EOT

1) PHP should have either mysqli or PDO mysql driver.
2) You have to set one or more custom configurations in app/config/parameters.
   Default name is 'production'.

EOT;
