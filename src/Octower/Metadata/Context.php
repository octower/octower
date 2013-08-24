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

abstract class Context
{
    protected $name;

    /**
     * All descendants' constructors should call this parent constructor
     *
     * @param string $name The Project's name
     */
    public function __construct($name)
    {
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