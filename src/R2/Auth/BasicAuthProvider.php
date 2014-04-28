<?php

namespace R2\Auth;

class BasicAuthProvider
{
    private $userClass;
    private $userList;
    private $user;

    /**
     * Constructor.
     *
     * @param string $userClass
     * @param array  $userList
     */
    public function __construct($userClass, $userList)
    {
        $this->userClass = $userClass;
        $this->userList  = $userList;
    }

    /**
     * Force user to login.
     *
     * @return boolean
     */
    public function authenticate()
    {
        $realm = 'Secured area';
        header('WWW-Authenticate: Basic realm="'.$realm.'"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'You are not logged in';

        return true;
    }

    /**
     * Get current user (authenticated or not).
     *
     * @return object
     */
    public function getUser()
    {
        if (!isset($this->user)) {
            // buggy PHP cannot access these values through filter_input()
            $authUsername = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
            $authPassword = isset($_SERVER['PHP_AUTH_PW'])   ? $_SERVER['PHP_AUTH_PW']   : null;

            $userClass = $this->userClass;
            $user = new $userClass(); // it's anonymous by default

            if (null !== $authUsername) {
                foreach ($this->userList as $id => $record) {
                    if ($record['username'] === $authUsername && $record['password'] === $authPassword) {
                        $user->id       = $id;
                        $user->username = $record['username'];
                        $user->password = $record['password'];
                        $user->groupId  = $record['group'];
                        break;
                    }
                }
            }
            $this->user = $user;
        }

        return $this->user;
    }
}
