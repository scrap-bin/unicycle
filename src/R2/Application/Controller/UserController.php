<?php

namespace R2\Application\Controller;

use R2\Application\Controller;
use R2\Security\HeaderAuthProviderInterface;

class UserController extends Controller
{
    private function getAfterLoginUrl()
    {
        $url = filter_input(INPUT_POST, 'redirect_url', FILTER_SANITIZE_URL) or
        $url = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL) or
        $url = $this->get('router')->url('homepage');

        return $url;
    }

    public function loginAction($matches)
    {
        /* @var R2\Security\UserProviderInterface */
        $up = $this->get('user_provider');
        $redirectUrl = $this->getAfterLoginUrl();
        // Is already logged in?
        if ($this->user->hasRole('ROLE_USER')) {
            $this->redirect($redirectUrl);
        }
        // weird! but working separation basic auth and others
        if ($up instanceof HeaderAuthProviderInterface) {
            $up->authenticate();
            return;
        }
        
        $this->render('User/login', ['redirect_url' => $redirectUrl]);
    }

    /**
     * Log in - check credentials
     * @param array $matches
     */
    public function loginCheckAction($matches)
    {
        $form = $this->collectPostForm(['username', 'password', 'redirect_url', 'save_pass']);
        /* @var $up R2\Security\UserProviderInterface */
        $up = $this->get('user_provider');

        try {
            /* @var $validator R2\Validator\Validator */
//            $validator = $this->get('validator');
//            $errors = $validator->validate($form, 'login');
            if (empty($errors)) {
                /* @var $user R2\Security\Userinterface */
                $newUser = $up->loadUserByUsernameAndPassword($form['username'], $form['password']);
                $authorized = true;
            }
        } catch (\Exception $e) {
            $errors[] = $this->i18n->t('Wrong user/pass', 'validators');
        }

        if (!empty($authorized)) {
            $expire = time() + (!empty($form['save_pass']) ? 1209600 : 1800);
            $up->rememberUser($newUser, $expire);
            $this->redirect($form['redirect_url'] ?: $this->getAfterLoginUrl());
        }
        
        $this->render('User/login', compact('errors') + $form);
    }

    public function logoutAction($matches)
    {
        $up      = $this->get('user_provider');
        $router  = $this->get('router');
        $request = $this->get('request');
        
        $link = $router->url('homepage');
        if ($matches['token'] === $this->user->getCsrfToken()) {
            // weird! but working separation basic auth and others
            if ($up instanceof HeaderAuthProviderInterface) {
                $link = $request->getScheme().'://log:out@'.$request->getHost().$request->getBaseUrl().$link;
            } else {
                $up->rememberUser($up->loadAnonymousUser(), time() + 31536000);
            }
        }

        $this->redirect($link);
    }
}
