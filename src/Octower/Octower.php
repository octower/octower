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
use Octower\Script\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Octower
 *
 * @author William Pottier <developer@william-pottier.fr>
 */
class Octower
{
    const VERSION = '@package_version@';
    const OCTOWER_FOLDER = './.octower';
    const OCTOWER_FILE = './octower.json';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var string
     */
    private $octowerFolder;

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

    /**
     * @param \Octower\Script\EventDispatcher $eventDispatcher
     *
     * @return Octower
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * @return \Octower\Script\EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param string $octowerFolder
     *
     * @return $this
     */
    public function setOctowerFolder($octowerFolder)
    {
        $this->octowerFolder = $octowerFolder;
        return $this;
    }

    /**
     * @return string
     */
    public function getOctowerFolder()
    {
        return $this->octowerFolder;
    }
}