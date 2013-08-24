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

use Octower\Metadata\Context;

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
     * @var Context
     */
    private $context;

    /**
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Octower\Metadata\Context $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return \Octower\Metadata\Context
     */
    public function getContext()
    {
        return $this->context;
    }
}