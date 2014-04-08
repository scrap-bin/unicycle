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

    function yaml_parse($input)
    {
        return (new \fallback\Yaml())->loadString($input);
    }

    function yaml_parse_file($file)
    {
        return (new \fallback\Yaml())->loadFile($file);
    }
}
