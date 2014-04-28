<?php

namespace R2\Security;

interface HeaderAuthProviderInterface
{
    /**
     * Force user to login.
     *
     * @return boolean
     */
    public function authenticate();
}
