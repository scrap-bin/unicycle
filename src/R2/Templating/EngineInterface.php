<?php

namespace R2\Templating;

interface EngineInterface
{
    public function render($name, array $parameters = []);
    public function fetch($name, array $parameters = []);
    public function exists($name);
}
