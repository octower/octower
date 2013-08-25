<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Ssh\Authentication;

use Octower\Ssh\Authentication;

/**
 * Host based file authentication
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
class HostBasedFile implements Authentication
{
    protected $username;
    protected $hostname;
    protected $publicKeyFile;
    protected $privateKeyFile;
    protected $passPhrase;
    protected $localUsername;

    /**
     * Constructor
     *
     * @param  string $username
     * @param  string $hostname
     * @param  string $publicKeyFile
     * @param  string $privateKeyFile
     * @param  string $passPhrase     An optional pass phrase for the key
     * @param  string $localUsername  An optional local usernale. If omitted,
     *                                the username will be used
     */
    public function __construct($username, $hostname, $publicKeyFile, $privateKeyFile, $passPhrase = null, $localUsername = null)
    {
        $this->username = $username;
        $this->hostname = $hostname;
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->passPhrase = $passPhrase;
        $this->localUsername = $localUsername;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($session)
    {
        return ssh2_auth_hostbased_file(
            $session,
            $this->username,
            $this->hostname,
            $this->publicKeyFile,
            $this->privateKeyFile,
            $this->passPhrase,
            $this->localUsername
        );
    }
}
