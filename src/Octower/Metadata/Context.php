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

use Symfony\Component\Yaml\Exception\RuntimeException;

abstract class Context
{
    protected $name;

    protected $rootPath;

    protected $scripts;

    /**
     * All descendants' constructors should call this parent constructor
     *
     * @param string $name The Project's name
     */
    public function __construct($name)
    {
        $this->scripts = array();
        $this->name = strtolower($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    public function getNormalizedName()
    {
        return static::underscore($this->name);
    }

    /**
     * @param mixed $rootPath
     *
     * @return Context
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }


    public function addScript($eventName, $command, $priority)
    {
        if (!in_array($eventName, static::getScriptEvents())) {
            throw new \UnexpectedValueException(sprintf('Event "%s" is not a valid octower project script event.', $eventName));
        }

        if (!isset($this->scripts[$eventName])) {
            $this->scripts[$eventName] = array();
        }

        $this->scripts[$eventName][] = array(
            'command'  => $command,
            'priority' => $priority
        );
    }

    /**
     * @return mixed
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * @return mixed
     */
    public function getScriptsForEvent($eventName)
    {
        if (!in_array($eventName, static::getScriptEvents())) {
            throw new \UnexpectedValueException(sprintf('Event "%s" is not a valid octower project script event.', $eventName));
        }

        return $this->scripts[$eventName];
    }


    /**
     * @throws \RuntimeException
     */
    public static function getScriptEvents()
    {
        throw new \RuntimeException('You need to implement getScriptEvents() function');

        return array();
    }

    /**
     * @param $event
     *
     * @return mixed
     * @throws \UnexpectedValueException
     */
    public function getScriptsByPriority($eventName)
    {
        if (!in_array($eventName, static::getScriptEvents())) {
            throw new \UnexpectedValueException(sprintf('Event "%s" is not a valid octower project script event.', $eventName));
        }

        if (!isset($this->scripts[$eventName])) {
            $this->scripts[$eventName] = array();
        }

        usort($this->scripts[$eventName], function ($s1, $s2) {
            if ($s1['priority'] == $s2['priority']) {
                return 0;
            }

            return $s1['priority'] < $s2['priority'] ? -1 : 1;
        });


        return $this->scripts[$eventName];
    }

    /**
     * Converts the Project into a readable and unique string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * A string to underscore.
     *
     * @param string $id The string to underscore
     *
     * @return string The underscored string
     */
    public static function underscore($id)
    {
        return strtolower(preg_replace(array('/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('\\1_\\2', '\\1_\\2'), strtr($id, array('_' => '.', '/' => '-'))));
    }
}