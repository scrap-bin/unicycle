<?php

namespace R2\Model;

use R2\Security\UserInterface;

class User implements UserInterface
{
    public $id       = 0;
    public $username = 'guest';
    public $password = '';
    public $groupId  = self::GROUP_GUEST;
    public $email    = '';
    public $realname = '';
    public $language = 'en';
    public $created  = 0;
    public $updated  = 0;

    public function getRoles()
    {
        $roles = [];
        if ($this->groupId == self::GROUP_GUEST) {
            $roles[] = 'ROLE_GUEST';
        } else {
            $roles[] = 'ROLE_USER';
            if ($this->groupId == self::GROUP_MOD) {
                $roles[] = 'ROLE_MOD';
            } elseif ($this->groupId == self::GROUP_ADMIN) {
                $roles[] = 'ROLE_ADMIN';
            }
        }

        return $roles;
    }

    public function hasRole($role)
    {
        $roles = $this->getRoles();

        return in_array($role, $roles);
    }

    /**
     * Returns the password used to authenticate the user.
     * This should be the encoded password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the user preffered locale.
     * @return string
     */
    public function getLocale()
    {
        return $this->language;
    }

    public function getCsrfToken()
    {
        // weird! implicitly depends on superglobal
        return sha1($this->id.sha1(filter_input(INPUT_SERVER, 'REMOTE_ADDR')));
    }
}
