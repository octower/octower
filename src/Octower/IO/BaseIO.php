<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\IO;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Octower\Config;

abstract class BaseIO implements IOInterface
{
    protected $authentications = array();

    /**
     * {@inheritDoc}
     */
    public function getAuthentications()
    {
        return $this->authentications;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthentication($server)
    {
        return isset($this->authentications[$server]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthentication($server)
    {
        if (isset($this->authentications[$server])) {
            return $this->authentications[$server];
        }

        return array('username' => null, 'password' => null);
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthentication($server, $username, $password = null)
    {
        $this->authentications[$server] = array('username' => $username, 'password' => $password);
    }

    /**
     * {@inheritDoc}
     */
    public function loadConfiguration(Config $config)
    {

    }
}