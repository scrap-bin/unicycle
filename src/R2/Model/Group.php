<?php

namespace R2\Model;

class Group
{
    public $id = 0;
    public $title = '';
    public $roles = '';

    /**
     * Returns list of group roles.
     *
     * @return array
     */
    public function getRoles()
    {
        return array_filter(array_map('trim', explode(',', $this->roles)));
    }

    /**
     * Has group role or at least one of given roles?
     *
     * @param mixed $role Role name or array of names
     *
     * @return Boolean
     */
    public function hasRole($role)
    {
        $roles = $this->getRoles();

        if (is_array($role)) {
            return count(array_intersect($roles, $role)) > 0;
        }

        return in_array($role, $roles);
    }
}
