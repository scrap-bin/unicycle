<?php

namespace R2\Application;

class WebApplication extends Controller
{

    /**
     * Handles HTTP request
     */
    public function handleRequest()
    {
        header('Content-type: text/html; charset=utf-8');
        $this->i18n->setLocale('ru');
        echo $this->i18n->t('Hello world');
    }
}
