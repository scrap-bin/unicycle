<?php

require __DIR__.'/../src/bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'unit\\';
    $base_dir = __DIR__.'/unit';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative = substr($class, $len);
        $file = $base_dir . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});
