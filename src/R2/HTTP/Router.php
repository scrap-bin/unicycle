<?php

namespace R2\HTTP;

class Router
{
    protected $routes;
    protected $defaults;

    public function __construct(array $routes)
    {
        $this->routes = self::flatten($routes);
        $this->defaults = [
            'path' => '',
            'controller' => '',
            'defaults' => [],
            'requirements' => []
        ];
    }

    public function match($pathInfo, $method = 'GET')
    {
        $matches = [];
        foreach ($this->routes as $name => &$route) {
            $route = array_merge($this->defaults, $route);
            $compiledRoute = self::compile($route);
            if ('' !== $compiledRoute['staticPrefix']
                && 0 !== \strpos($pathInfo, $compiledRoute['staticPrefix'])) {
                continue;
            }
            if (!preg_match($compiledRoute['regex'], $pathInfo, $matches)) {
                continue;
            }
            if (isset($route['methods'])) {
                if ('HEAD' === $method) {
                    $method = 'GET';
                }
                if (!in_array($method, $route['methods'])) {
                    continue;
                }
            }
            $matches['_route']      = $name;
            $matches['_controller'] = $route['controller'];

            return self::mergeDefaults($matches, $route['defaults']);
        }

        return false;
    }

    public function url($name, array $parameters = [])
    {
        if (!array_key_exists($name, $this->routes)) {
            throw new \InvalidArgumentException(sprintf('Route name "%s" does not exist.', $name));
        }

        $route = array_merge($this->defaults, $this->routes[$name]);
        $compiledRoute = self::compile($route);

        $variables = array_flip($compiledRoute['variables']);
        $defaults  = $route['defaults'];
        $tokens    = $compiledRoute['tokens'];

        $mergedParams = array_replace($defaults, $parameters);

        if (count($diff = array_diff_key($variables, $mergedParams))) {
            $message = sprintf(
                'Some mandatory parameters are missing ("%s") to generate a URL for route "%s".',
                implode('", "', array_keys($diff)),
                $name
            );
            throw new \InvalidArgumentException($message);
        }

        $url = '';
        $optional = true;
        foreach ($tokens as $token) {
            if ('variable' === $token[0]) {
                if (!$optional
                    || !array_key_exists($token[3], $defaults)
                    || (null !== $mergedParams[$token[3]]
                        && (string) $mergedParams[$token[3]] !== (string) $defaults[$token[3]])
                 ) {
                    if (!preg_match('#^'.$token[2].'$#', $mergedParams[$token[3]])) {
                        $message = sprintf(
                            'Parameter "%s" for route "%s" must match "%s" ("%s" given) to generate an URL.',
                            $token[3],
                            $name,
                            $token[2],
                            $mergedParams[$token[3]]
                        );
                        throw new \InvalidArgumentException($message);
                    }
                    $url = $token[1].$mergedParams[$token[3]].$url;
                    $optional = false;
                }
            } else {
                $url = $token[1].$url;
                $optional = false;
            }
        }

        $extra = array_diff_key($parameters, $variables, $defaults);
        if ($extra && $query = http_build_query($extra, '', '&')) {
            $url .= '?'.$query;
        }

        return $url;
    }

    private static function flatten($array, array $result = [], $prefix = '')
    {
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                continue;
            }
            if ($key{0} == '/') {
                $result = self::flatten($value, $result, $prefix.$key);
            } else {
                if (array_key_exists('path', $value)) {
                    $value['path'] = $prefix.$value['path'];
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private static function compile(&$route)
    {
        if (isset($route['compiled'])) {
            return $route['compiled'];
        }

        $tokens = [];
        $variables = [];
        $matches = [];
        $pos = 0;

        $pattern = $route['path'];
        $defaults = $route['defaults'];
        $requirements = $route['requirements'];

        preg_match_all('#\{\w+\}#', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $match) {
            $varName = substr($match[0][0], 1, -1);
            $precedingText = substr($pattern, $pos, $match[0][1] - $pos);
            $pos = $match[0][1] + strlen($match[0][0]);
            $precedingChar = strlen($precedingText) > 0 ? substr($precedingText, -1) : '';
            $isSeparator = '' !== $precedingChar && false !== strpos('/,;.:-_~+*=@|', $precedingChar);

            if (is_numeric($varName)) {
                throw new \DomainException(
                    sprintf(
                        'Variable name "%s" cannot be numeric in route pattern "%s". Please use a different name.',
                        $varName,
                        $pattern
                    )
                );
            }
            if (in_array($varName, $variables)) {
                throw new \LogicException(
                    sprintf(
                        'Route pattern "%s" cannot reference variable name "%s" more than once.',
                        $pattern,
                        $varName
                    )
                );
            }

            if ($isSeparator && strlen($precedingText) > 1) {
                $tokens[] = ['text', substr($precedingText, 0, -1)];
            } elseif (!$isSeparator && strlen($precedingText) > 0) {
                $tokens[] = ['text', $precedingText];
            }

            $regexp = isset($requirements[$varName]) ? $requirements[$varName] : null;
            if (null === $regexp) {
                $followingPattern = (string) substr($pattern, $pos);
                $nextSeparator = self::findNextSeparator($followingPattern);
                $regexp = sprintf(
                    '[^%s%s]+',
                    '/',
                    '/' !== $nextSeparator && '' !== $nextSeparator ? preg_quote($nextSeparator, '#') : ''
                );
                if (('' !== $nextSeparator && !preg_match('#^\{\w+\}#', $followingPattern))
                    || '' === $followingPattern) {
                    $regexp .= '+';
                }
            }

            $tokens[] = ['variable', ($isSeparator ? $precedingChar : ''), $regexp, $varName];
            $variables[] = $varName;
        }

        if ($pos < strlen($pattern)) {
            $tokens[] = ['text', substr($pattern, $pos)];
        }

        $firstOptional = PHP_INT_MAX;
        $defaultVarNames = array_keys($defaults);
        for ($i = count($tokens) - 1; $i >= 0; $i--) {
            $token = $tokens[$i];
            if ('variable' === $token[0] && in_array($token[3], $defaultVarNames)) {
                $firstOptional = $i;
            } else {
                break;
            }
        }

        $regexp = '';
        for ($i = 0, $nbToken = count($tokens); $i < $nbToken; $i++) {
            $regexp .= self::computeRegexp($tokens, $i, $firstOptional);
        }

        return $route['compiled'] = [
            'staticPrefix'  => 'text' === $tokens[0][0] ? $tokens[0][1] : '',
            'regex'         => '#^'.$regexp.'$#s',
            'tokens'        => array_reverse($tokens),
            'variables'     => $variables,
        ];
    }

    private static function computeRegexp(array $tokens, $index, $firstOptional)
    {
        $token = $tokens[$index];
        if ('text' === $token[0]) {
            return preg_quote($token[1], '#');
        } else {
            if (0 === $index && 0 === $firstOptional) {
                return sprintf('%s(?P<%s>%s)?', preg_quote($token[1], '#'), $token[3], $token[2]);
            } else {
                $regexp = sprintf('%s(?P<%s>%s)', preg_quote($token[1], '#'), $token[3], $token[2]);
                if ($index >= $firstOptional) {
                    $regexp = "(?:$regexp";
                    $nbTokens = count($tokens);
                    if ($nbTokens - 1 == $index) {
                        $regexp .= str_repeat(")?", $nbTokens - $firstOptional - (0 === $firstOptional ? 1 : 0));
                    }
                }

                return $regexp;
            }
        }
    }

    private static function findNextSeparator($pattern)
    {
        if ('' == $pattern) {
            return '';
        }
        $pattern = preg_replace('#\{\w+\}#', '', $pattern);

        return isset($pattern[0]) && false !== strpos('/,;.:-_~+*=@|', $pattern[0]) ? $pattern[0] : '';
    }

    private static function mergeDefaults($params, $defaults)
    {
        foreach ($params as $key => $value) {
            if (!is_int($key)) {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}
