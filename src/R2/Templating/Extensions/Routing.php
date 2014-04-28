<?php

namespace R2\Templating\Extensions;

class Routing
{
    public static $router;
    public static $request;

    public function __construct($router, $request)
    {
        self::$router = $router;
        self::$request = $request;
        require_once __DIR__ . '/routing_shortcuts.php';
    }
}
