<?php

namespace unit\R2\Security;

use R2\Security\Helper;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    private function do100times($len, $entropy, $pattern)
    {
        for ($i = 0; $i < 100; ++$i) {
            $key = Helper::randomKey($len, $entropy);
            if (\strlen($key) != $len || !preg_match($pattern, $key)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @covers R2\Security\Helper::randomKey
     */
    public function testRandomKey()
    {
        // false  - all visible ASCII characters are used
        for ($len = 1; $len < 10; ++$len) {
            $this->assertTrue($this->do100times($len, false, '/^[\x{21}-\x{7E}]+$/'));
        }
        $this->assertFalse($this->do100times($len, false, '/^[0-9A-Za-z]+$/'));
        // true   - digits and latin characters in both cases
        $this->assertTrue($this->do100times(10, true, '/^[0-9a-zA-Z]+$/'));
        // 2      - binary number
        $this->assertTrue($this->do100times(10, 2, '/^[01]+$/'));
        // 10     - decimal
        $this->assertTrue($this->do100times(10, 10, '/^\d+$/'));
        // 16     - hexadecimal
        $this->assertTrue($this->do100times(10, 16, '/^[0-9A-F]+$/'));
        // 36     - 0-9A-Z
        $this->assertTrue($this->do100times(10, 36, '/^[0-9A-Z]+$/'));
        // >=62   - the same as in "true" case
        $this->assertTrue($this->do100times(10, 9000, '/^[0-9A-Za-z]+$/'));
    }
}
