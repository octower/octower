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
 * Password Authentication
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
class Password implements Authentication
{
    protected $username;
    protected $password;

    /**
     * Constructor
     *
     * @param  string $username The authentication username
     * @param  string $password The authentication password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($session)
    {
        // Hide auth warning
        return @ssh2_auth_password($session, $this->username, $this->password);
    }
}
