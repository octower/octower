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

use RuntimeException;

/**
 * An abstract resource holder
 *
 * @author Antoine Hérault <antoine.herault@gmail.com>
 */
abstract class AbstractResourceHolder implements ResourceHolder
{
    protected $resource;

    /**
     * Returns the underlying resource. If the resource does not exist, it will
     * create it
     *
     * @return resource
     */
    public function getResource()
    {
        if (!is_resource($this->resource)) {
            $this->createResource();
        }

        return $this->resource;
    }

    /**
     * Creates the underlying resource
     *
     * @throws RuntimeException on resource creation failure
     */
    abstract protected function createResource();
}
