<?php

mb_internal_encoding('UTF-8');

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        include $file;
    }
});

// if PECL YAML not intalled:
if (!function_exists('yaml_parse_file')) {
    include __DIR__.'/fix_yaml.php';
}
