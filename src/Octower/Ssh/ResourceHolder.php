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
 * Interface that must be implemented by that handle a resource
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
interface ResourceHolder
{
    /**
     * Returns the underlying resource
     *
     * @return resource
     */
    function getResource();
}
