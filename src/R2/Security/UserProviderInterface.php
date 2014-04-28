<?php

namespace R2\Security;

interface UserProviderInterface
{
    /**
     * Creates new user.
     *
     * @return UserInterface
     */
    public function newUser();
    /**
     * Loads the user for by the auth cookie.
     * It loads appropriate user or, in error, anonymous user
     *
     * @return UserInterface
     */
    public function loadIdentifiedUser();
    /**
     * Loads the user for the given ID.
     *
     * @param string $id The ID
     *
     * @return UserInterface
     */
    public function loadUserById($id);
    /**
     * Loads the user for the given username.
     *
     * @param string $username The username
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username);
    /**
     * Loads the user for the given email.
     *
     * @param string $email The email
     *
     * @return UserInterface
     */
    public function loadUserByEmail($email);
    /**
     * Loads anonymous user.
     *
     * @return UserInterface
     */
    public function loadAnonymousUser();
}
