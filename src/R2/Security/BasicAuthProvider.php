<?php

namespace R2\Security;

class BasicAuthProvider implements UserProviderInterface, HeaderAuthProviderInterface
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
     * Creates new user.
     *
     * @return UserInterface
     */
    public function newUser()
    {
        $userClass = $this->userClass;

        return new $userClass();
    }

    /**
     * Get current user (authenticated or not).
     *
     * @return object
     */
    public function loadIdentifiedUser()
    {
        if (!isset($this->user)) {
            // buggy PHP cannot access these values through filter_input()
            $authUsername = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
            $authPassword = isset($_SERVER['PHP_AUTH_PW'])   ? $_SERVER['PHP_AUTH_PW']   : null;

            $user = $this->newUser();

            if (null !== $authUsername) {
                try {
                    $u = $this->loadUserByUsername($authUsername);
                    if ($u->password == $authPassword) {
                        $user = $u;
                    }
                } catch (\Exception $ex) {
                }
            }
            $this->user = $user;
        }

        return $this->user;
    }

    /**
     * Loads the user for the given ID.
     *
     * @param string $id The ID
     *
     * @return UserInterface
     */
    public function loadUserById($id)
    {
        foreach ($this->userList as $record) {
            if ($id == $record['id']) {
                $user = $this->newUser();
                $user->id       = $record['id'];
                $user->username = $record['username'];
                $user->password = $record['password'];
                $user->groupId  = $record['group'];

                return $user;
            }
        }
        throw new \InvalidArgumentException('Empty result');
    }

    /**
     * Loads the user for the given username.
     *
     * @param string $username The username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        foreach ($this->userList as $record) {
            if ($username == $record['username']) {
                $user = $this->newUser();
                $user->id       = $record['id'];
                $user->username = $record['username'];
                $user->password = $record['password'];
                $user->groupId  = $record['group'];

                return $user;
            }
        }
        throw new \InvalidArgumentException('Empty result');
    }

    /**
     * Loads the user for the given email
     *
     * @param string $email The email
     *
     * @return UserInterface
     */
    public function loadUserByEmail($email)
    {
        foreach ($this->userList as $record) {
            if ($email == $record['email']) {
                $user = $this->newUser();
                $user->id       = $record['id'];
                $user->username = $record['username'];
                $user->password = $record['password'];
                $user->groupId  = $record['group'];

                return $user;
            }
        }
        throw new \InvalidArgumentException('Empty result');
    }

    /**
     * Loads anonymous user.
     *
     * @return UserInterface
     */
    public function loadAnonymousUser()
    {
        $user = $this->newUser();
        $class = $this->userClass;
        $user->id = $class::GUEST_ID;

        return $user;
    }
}
