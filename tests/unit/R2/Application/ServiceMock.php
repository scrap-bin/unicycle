<?php

namespace unit\R2\Application;

class ServiceMock
{
    public $arg;

    public function __construct($arg)
    {
        $this->arg = $arg;
    }
}
