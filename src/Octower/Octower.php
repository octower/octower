<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower;

/**
 * Class Octower
 *
 * @author William Pottier <developer@william-pottier.fr>
 */
class Octower
{
    const VERSION = '@package_version@';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

}