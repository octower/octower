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
 * Public key file authentication
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
class PublicKeyFile implements Authentication
{
    protected $username;
    protected $publicKeyFile;
    protected $privateKeyFile;
    protected $passPhrase;

    /**
     * Constructor
     *
     * @param  string $username       The authentication username
     * @param  string $publicKeyFile  The path of the public key file
     * @param  string $privateKeyFile The path of the private key file
     * @param  string $passPhrase     An optional pass phrase for the key
     */
    public function __construct($username, $publicKeyFile, $privateKeyFile, $passPhrase = null)
    {
        $this->username = $username;
        $this->publicKeyFile = $publicKeyFile;
        $this->privateKeyFile = $privateKeyFile;
        $this->passPhrase = $passPhrase;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($session)
    {
        return @ssh2_auth_pubkey_file(
            $session,
            $this->username,
            $this->publicKeyFile,
            $this->privateKeyFile,
            $this->passPhrase
        );
    }
}
