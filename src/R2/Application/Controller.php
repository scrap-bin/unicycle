<?php

namespace R2\Application;

use R2\DependencyInjection\ContainerInterface;
use R2\DependencyInjection\ContainerAwareInterface;

class Controller implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;
    /** @var \R2\Translation\TranslatorInterface */
    protected $i18n;
    /** @var \R2\Security\UserInterface */
    protected $user;
    /** @var \R2\ORM\EntityManagerInterface */
    protected $entityManager;
    /** @var \R2\Model\LookupHelper */
    protected $lookup;

    /**
     * Sets the Container associated with this Controller.
     * Provides a fluent interface.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @return Controller
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->i18n          = $container->get('i18n');
//        $this->user          = $container->get('user');
        $this->entityManager = $container->get('entity_manager');
//        $this->lookup        = $container->get('lookup_helper');
        return $this;
    }

    /**
     * Gets a service.
     *
     * @param  string $id The service identifier
     * @return object The associated service
     */
    public function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Gets a parameter.
     *
     * @param string $name The parameter name
     *
     * @return mixed The parameter value
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    public function render($view, array $parameters = [])
    {
        return $this->container->get('templating')->render($view, $parameters);
    }

    public function renderAndExit($view, array $parameters = [])
    {
        $this->container->get('templating')->render($view, $parameters);
        exit();
    }

    public function fetch($view, array $parameters = [])
    {
        return $this->container->get('templating')->fetch($view, $parameters);
    }

    /**
     * Do a HTTP redirect response to the given URL
     * Note: on a target the Referer header will be set to preceding URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     */
    public function redirect($url, $status = 302)
    {
        if (0 !== strpos($url, 'http')) {
            $request = $this->get('request');
            $url = $request->getSchemeAndHttpHost().$request->getBaseUrl().$url;
        }
        header('Location: '.$url, true, $status);
        exit();
    }

    /**
     * Response by HTML redirecting page.
     * Note: on a target the Referer header will be set to bouncing (i.e. this) URL.
     *
     * @param string $url     The URL to redirect to
     * @param string $message The message to show
     * @param int    $delay   Seconds to redirect. 0 - no wait
     */
    public function redirectWithMessage($url, $message = '', $delay = 0)
    {
        if (0 !== strpos($url, 'http')) {
            $request = $this->get('request');
            $url = $request->getSchemeAndHttpHost().$request->getBaseUrl().$url;
        }
        if (empty($message)) {
            $message = $this->i18n->t('Redirecting');
        }

        $this->renderAndExit('Default/redirect_message', compact('url', 'delay', 'message'));
    }

    /**
     * Show message and stop.
     *
     * @param string $message The message to show
     */
    public function message($message)
    {
        $this->renderAndExit('Default/message', compact('message'));
    }

    /**
     * Helper: grab form fields for validation and persistence
     *
     * @param array $names
     */
    public function collectPostForm($names)
    {
        $defaults = array_fill_keys($names, null);
        $result = array_intersect_key($_POST, $defaults) + $defaults;
        array_walk_recursive(
            $result,
            function (&$item, $key) {
                $item = trim($item);
            }
        );

        return $result;
    }
}
