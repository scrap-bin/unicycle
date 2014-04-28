<?php

namespace R2\Application\Controller;

use R2\Application\Controller;

class ProfileController extends Controller
{
    public function indexAction($matches)
    {
        $this->render('Profile/index');
    }
}
