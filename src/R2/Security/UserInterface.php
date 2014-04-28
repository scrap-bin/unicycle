<?php

namespace R2\Security;

interface UserInterface
{
    const GROUP_UNVERIFIED  = 0;
    const GROUP_ADMIN       = 1;
    const GROUP_MOD         = 2;
    const GROUP_GUEST       = 3;
    const GROUP_USER        = 4;
    const GUEST_ID          = 1;
    /**
     * Returns the roles granted to the user
     * @return string[]
     */
    public function getRoles();

    /**
     * Check if user has the role
     * @return string[]
     */
    public function hasRole($value);

    /**
     * Returns the password used to authenticate the user
     * This should be the encoded password
     * @return string
     */
    public function getPassword();

    /**
     * Returns the username used to authenticate the user
     * @return string The username
     */
    public function getUsername();

    /**
     * Returns the user preffered locale
     * @return string
     */
    public function getLocale();
}
