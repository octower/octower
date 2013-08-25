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
 * SSH Agent authentication
 *
 * @author Cam Spiers <camspiers@gmail.com>
 */
class Agent implements Authentication
{
    protected $username;

    /**
     * Constructor
     *
     * @param  string $username       The authentication username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($session)
    {
        return ssh2_auth_agent(
            $session,
            $this->username
        );
    }
}
