<?php

namespace R2\Application;

class WebApp extends Controller
{

    public function construct()
    {
        parent::__construct();
    }

    /**
     * Handles HTTP request
     */
    public function handleRequest()
    {
        echo 'Hello world!';
    }
}
