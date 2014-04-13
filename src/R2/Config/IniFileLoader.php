<?php

namespace R2\Config;

use InvalidArgumentException;

class IniFileLoader implements FileLoaderInterface
{
    /**
     * Checks if such file type is supported.
     * 
     * @param  string  $resource
     * 
     * @return Boolean
     */
    public function supports($resource)
    {
        return is_string($resource) && 'ini' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Loads data.
     *
     * @param string $resource The filename
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function load($resource)
    {
        if (!file_exists($resource)) {
            throw new InvalidArgumentException('Resource not found');
        }
        $result = [];
        $tmp = parse_ini_file($resource, true);
        ksort($tmp);
        foreach ($tmp as $section => $values) {
            $x =& $result;
            foreach (explode('.', $section) as $s) {
                if (!array_key_exists($s, $x)) {
                    $x[$s] = [];
                }
                $x =& $x[$s];
            }
            $x = $values;
        }

        return $result;
    }
}
