<?php

namespace R2\Security;

class Helper
{
    const SET_ASCII       = false;
    const SET_ALPHANUM    = true;
    const SET_BINARY      = 2;
    const SET_DECIMAL     = 10;
    const SET_HEXADECIMAL = 16;
    const SET_UPALPHANUM  = 36;
    
    private static $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * Key string generator.
     * Second parameter can be boolean or integer and define what result is:
     *   false  - all visible ASCII characters are used
     *   true   - digits and latin characters in both cases
     *   2      - binary number
     *   10     - decimal
     *   16     - hexadecimal
     *   36     - 0-9A-Z
     *   >=62   - the same as in "true" case
     * 
     * @param int     $len     Result length in bytes
     * @param mixed   $entropy Measure of entropy. See notes above.
     * 
     * @return string
     */
    public static function randomKey($len, $entropy = false)
    {
        $key = '';
        if ($entropy) {
            $num = is_int($entropy) && ($entropy >= 2 && $entropy <= 62)
                ? $entropy
                : 62;
            for ($i = 0; $i < $len; ++$i) {
                $key .= self::$chars{mt_rand() % $num};
            }
        } else {
            for ($i = 0; $i < $len; ++$i) {
                $key .= chr(mt_rand(33, 126));
            }
        }

        return $key;
    }
}
