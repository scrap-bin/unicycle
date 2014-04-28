<?php
/*
 * Some usefull functions with short and clear names.
 *
 * DO NOT INCLUDE THIS FILE DIRECTLY
 * It depends on static var of Standard Extension class
 */
use R2\Templating\Extensions\Routing;

/**
 * Shortcut to URL generator
 * @param string $name
 * @param array $parameters
 * @return string
 */
function url($name, array $parameters = [])
{
    return baseUrl().Routing::$router->url($name, $parameters);
}

/**
 * Shortcut to Base URL
 * @return string
 */
function baseUrl()
{
    static $baseUrl;
    if (!isset($baseUrl)) {
        $baseUrl = Routing::$request->getBaseUrl();
    }

    return $baseUrl;
}

/**
 * Shortcut to Scheme and Host
 * @return string
 */
function schemeAndHost()
{
    static $schemeAndHost;
    if (!isset($schemeAndHost)) {
        $schemeAndHost = Routing::$request->getSchemeAndHttpHost();
    }

    return $schemeAndHost;
}
