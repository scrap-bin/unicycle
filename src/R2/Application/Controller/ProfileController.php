<?php

namespace R2\Application\Controller;

use R2\Application\Controller;

class ProfileController extends Controller
{
    public function indexAction($matches)
    {
        $this->render('Profile/index');
    }

    public function loginAction($matches)
    {
        $userProvider = $this->get('user_provider');
        $user = $userProvider->getUser();
        if (!$user->hasRole('ROLE_USER')) {
            $userProvider->authenticate();

            return;
        }
        $redirectUrl = filter_input(INPUT_POST, 'redirect_url', FILTER_SANITIZE_URL) or
        $redirectUrl = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL) or
        $redirectUrl = $this->get('router')->url('homepage');

        $this->redirect($redirectUrl);
    }

    public function logoutAction($matches)
    {
        $router = $this->get('router');
        $request = $this->get('request');
        // Trick: try to log in with fake credentials
        $redirectUrl =
            $request->getScheme().'://log:out@'
            .$request->getHost().$request->getBaseUrl()
            .$router->url('homepage');

        $this->redirect($redirectUrl);
    }
}
