<?php

namespace R2\Application\Controller;

use R2\Application\Controller;

class FrontPageController extends Controller
{
    public function indexAction($matches)
    {
        $this->render('FrontPage/index');
    }
}
