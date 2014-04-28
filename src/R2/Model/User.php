<?php

namespace R2\Model;

class User
{
    const GROUP_UNVERIFIED  = 0;
    const GROUP_ADMIN       = 1;
    const GROUP_MOD         = 2;
    const GROUP_GUEST       = 3;
    const GROUP_USER        = 4;

    public $id       = 0;
    public $username = 'guest';
    public $password = '';
    public $groupId  = self::GROUP_GUEST;
    public $email    = '';
    public $realname = '';
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
}
