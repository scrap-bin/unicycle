<?php

namespace R2\Security;

use R2\ORM\EntityManagerInterface;

class FluxUserProvider implements UserProviderInterface
{
    protected $entityManager;
    protected $userClass;
    protected $cookieName;
    protected $cookieDomain;
    protected $cookiePath;
    protected $cookieSecure;
    protected $cookieSeed;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $userClass
     * @param array                  $cookieParams
     */
    public function __construct(EntityManagerInterface $entityManager, $userClass, array $cookieParams)
    {
        $this->entityManager  = $entityManager;
        $this->userClass      = $userClass;
        $this->cookieName     = $cookieParams['cookie_name'];
        $this->cookieDomain   = $cookieParams['cookie_domain'];
        $this->cookiePath     = $cookieParams['cookie_path'];
        $this->cookieSecure   = $cookieParams['cookie_secure'];
        $this->cookieSeed     = $cookieParams['cookie_seed'];
    }

    /**
     * Creates new user.
     *
     * @return UserInterface
     */
    public function newUser()
    {
        $user = new User();
        $user->registered = $user->lastVisit = time();
        $user->registrationIp = filter_input(INPUT_SERVER, 'REMOTE_ADDR');

        return $user;
    }

    /** @var UserInterface */
    protected $user;

    /**
     * Loads the user for by the auth cookie.
     * It loads appropriate user or, in error, anonymous user
     *
     * @return UserInterface
     */
    public function loadIdentifiedUser()
    {
        $now = time();
        if (isset($this->user)) {
            return $this->user;
        }

        $cookieValue = filter_input(INPUT_COOKIE, $this->cookieName) ?: '';
        $matches = null;
        if (preg_match('%^(\d+)\|([0-9a-fA-F]+)\|(\d+)\|([0-9a-fA-F]+)$%', $cookieValue, $matches)) {
            $userId			= intval($matches[1]);
            $passwordHash   = $matches[2];
            $expirationTime = intval($matches[3]);
            $cookieHash		= $matches[4];
            if ($userId > 1 && $expirationTime > time()) {
                $data = $userId.'|'.$expirationTime;
                $key = $this->cookieSeed.'_cookie_hash';
                if (hash_hmac('sha1', $data, $key, false) === $cookieHash) {
                    try {
                        $user = $this->entityManager->find($this->userClass, $userId);
                    } catch (\Exception $ex) {
                    }
                    if (isset($user) && !empty($user->id)) {
                        $data = $user->password;
                        $key = $this->cookieSeed.'_password_hash';
                        if (hash_hmac('sha1', $data, $key, false) === $passwordHash) {
                            // Renew cookie
                            $expire = ($expirationTime > $now + 1800) ? $now + 1209600 : $now + 1800;
                            $this->rememberUser($user, $expire);

                            return $this->user = $user;
                        }
                    }
                }
            }
        }

        // Set anonymous and save its cookie
        $user = $this->loadAnonymousUser();
        $this->rememberUser($user, $now + 31536000);

        return $this->user = $user;
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
        return $this->entityManager->find($this->userClass, $id);
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
        return $this->entityManager->findOneBy($this->userClass, ['username' => $username]);
    }

    /**
     * Loads the user for the given email.
     *
     * @param string $email The email
     *
     * @return UserInterface
     */
    public function loadUserByEmail($email)
    {
        return $this->entityManager->findOneBy($this->userClass, ['email' => $email]);
    }

    /**
     * Loads the user for the given username and password.
     *
     * @param string $username The username
     * @param string $password The password
     *
     * @return UserInterface
     * @throws \InvalidArgumentException
     */
    public function loadUserByUsernameAndPassword($username, $password)
    {
        $user = $this->loadUserByUsername($username);
        if (sha1($password) == $user->password) {
            return $user;
        }
        throw new \InvalidArgumentException('Invalid username or password');
    }

    public function rememberUser($user, $expire)
    {
        $value = $user->id.'|'
           .hash_hmac('sha1', $user->password, $this->cookieSeed.'_password_hash', false)
           .'|'.$expire.'|'
           .hash_hmac('sha1', $user->id.'|'.$expire, $this->cookieSeed.'_cookie_hash', false);
        setcookie(
            $this->cookieName,
            $value,
            $expire,
            $this->cookiePath,
            $this->cookieDomain,
            $this->cookieSecure,
            true
        );
    }

    /**
     * Loads anonymous user.
     *
     * @return UserInterface
     */
    public function loadAnonymousUser()
    {
        $class = $this->userClass;
        $user = $this->entityManager->find($class, $class::GUEST_ID);
        $user->password = sha1(uniqid()); // just for fun

        return $user;
    }
}
