<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Script;

use Octower\IO\IOInterface;
use Octower\Octower;

class Event
{
    const EVENT_PRE_PACKAGE  = 'pre-package';
    const EVENT_POST_EXTRACT = 'post-extract';
    const EVENT_PRE_ENABLE   = 'pre-enable';
    const EVENT_POST_ENABLE  = 'post-enable';
    const EVENT_PRE_DISABLE  = 'pre-disable';
    const EVENT_POST_DISABLE = 'post-disable';

    /**
     * @var string This event's name
     */
    private $name;

    /**
     * @var Octower The octower instance
     */
    private $octower;

    /**
     * @var IOInterface The IO instance
     */
    private $io;

    /**
     * @var boolean Dev mode flag
     */
    private $devMode;

    /**
     * Constructor.
     *
     * @param string      $name     The event name
     * @param Octower     $octower  The octower object
     * @param IOInterface $io       The IOInterface object
     * @param boolean     $devMode  Whether or not we are in dev mode
     */
    public function __construct($name, Octower $octower, IOInterface $io, $devMode = false)
    {
        $this->name    = $name;
        $this->octower = $octower;
        $this->io      = $io;
        $this->devMode = $devMode;
    }

    /**
     * Returns the event's name.
     *
     * @return string The event name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the octower instance.
     *
     * @return Octower
     */
    public function getOctower()
    {
        return $this->octower;
    }

    /**
     * Returns the IO instance.
     *
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * Return the dev mode flag
     *
     * @return boolean
     */
    public function isDevMode()
    {
        return $this->devMode;
    }
}