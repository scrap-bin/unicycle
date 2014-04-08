<?php

echo 'PHP version ...... '.(version_compare(PHP_VERSION, '5.4', '>=') ? 'OK' : 'ERROR')."\n";

echo 'JSON support ..... '.(function_exists('json_encode') ? 'OK' : 'ERROR')."\n";

echo 'YAML support ..... '.(function_exists('yaml_parse_file')
        ? 'OK'
        : (file_exists(__DIR__.'/../src/fallback/Yaml.php')
            ? 'WARNING. Will use a workaround.'
            : 'ERROR'))."\n";

echo 'mysqli support ... '.(function_exists('mysqli_connect') ? 'OK' : 'ERROR')."\n";

echo 'Cache writable ... '.(is_writable(__DIR__.'/../app/cache') ? 'OK' : 'ERROR. Chmod +x to app/cache!')."\n";

echo 'Configuration(*) . '.(count(glob(__DIR__.'/../app/config/parameters/*.yml'))
        ? 'OK'
        : 'ERROR. Define at least one!')."\n";

echo <<<EOT

* You have to set one or more custom configurations in app/config/parameters.
  Default name is 'production'.

EOT;
