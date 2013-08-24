<?php
/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Metadata;

use Octower\Remote\ServerInterface;

class Project extends Context
{
    protected $version;

    protected $excluded;

    protected $shared;

    protected $remotes;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->remotes = array();
    }

    /**
     * @param mixed $excluded
     *
     * @return Project
     */
    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

    /**
     * @param mixed $shared
     *
     * @return Project
     */
    public function setShared($shared)
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * @param mixed $version
     *
     * @return Project
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $remote
     *
     * @return Project
     */
    public function addRemotes($name, $remote)
    {
        $this->remotes[$name] = $remote;

        return $this;
    }

    /**
     * @param $name
     *
     * @return ServerInterface
     */
    public function getRemote($name)
    {
        return $this->remotes[$name];
    }

}