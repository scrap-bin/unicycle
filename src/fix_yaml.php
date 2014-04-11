<?php

function yaml_parse($input)
{
    return (new \fallback\Yaml())->loadString($input);
}

function yaml_parse_file($file)
{
    return (new \fallback\Yaml())->loadFile($file);
}
