<?php

namespace R2\Application;

use R2\DependencyInjection\ContainerAwareInterface;

class WebApplication extends Controller
{

    /**
     * Handles HTTP request
     */
    public function handleRequest()
    {

        try {
            /* @var $request R2\Http\Request */
            $request = $this->get('request');
            $pathInfo = $request->getPathinfo();
            // Keep referral code hidden
            $requestMethod = $request->getMethod();
            $accessRules = $this->getParameter('security.access_control');

            if (!$this->checkAccess($pathInfo, $this->user->getRoles(), $accessRules)) {
                if ($this->user->hasRole('ROLE_USER')) {
                    $this->renderAndExit('Default/error403');
                }
                // Show login dialog instead of secured page
                $this->redirectWithMessage($this->get('router')->url('login'));
            }
            if (!$this->doRoute($pathInfo, $requestMethod)) {
                $this->renderAndExit('Default/error404');
            }
        } catch (\Exception $ex) {
            if ($this->getParameter('parameters.debug')) {
                $message = $ex->getMessage();
            } else {
                $message = 'Some error happens.';
            }
            $this->get('db')->rollback();
            $this->message($message);
        }
    }

    /**
     * Route request to appropriate controller action
     * @param  string  $pathInfo      Request URI (without base prefix)
     * @param  string  $requestMethod Request method
     * @return boolean Success
     */
    private function doRoute($pathInfo, $requestMethod)
    {
        $matches = $this->get('router')->match($pathInfo, $requestMethod);
        if (isset($matches[ '_controller'])) {
            list($namespace, $controller, $action) = array_map('trim', explode(':', $matches[ '_controller']));
            if ($namespace === '') {
                $namespace = __NAMESPACE__;
            } elseif (false === strpos($namespace, '\\')) {
                $namespace = $this->getParameter('synonyms.'.$namespace);
            }
            $class = "{$namespace}\\{$controller}Controller";
            $method = "{$action}Action";
            if (class_exists($class) && is_callable([$class, $method])) {
                $object = new $class();
                if ($object instanceof ContainerAwareInterface) {
                    $object->setContainer($this->container);
                }
                $object->$method($matches);

                return true;
            }
        }

        return false;
    }

    /**
     * Check given URI against defined access list
     * @param  string  $pathInfo Request URI (without base prefix)
     * @param  array   $roles    User roles
     * @return boolean Do user permit this?
     */
    private function checkAccess($pathInfo, array $roles, array $accessRules)
    {
        if (empty($accessRules)) {
            return true;
        }
        foreach ($accessRules as $item) {
            $pattern = '#'.$item['path'].'#';
            if (preg_match($pattern, $pathInfo)) {
                if ($item['role'] === 'IS_AUTHENTICATED_ANONYMOUSLY'
                    || array_intersect($roles, (array) $item['role'])) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }
}
