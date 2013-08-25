<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Ssh;

/**
 * Interface that must be implemented by the authentication classes
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
interface Authentication
{
    /**
     * Authenticates the given SSH session
     *
     * @param  resource $session
     *
     * @return Boolean TRUE on success, or FALSE on failure
     */
    function authenticate($session);
}
