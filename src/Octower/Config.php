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

use Octower\Config\ConfigSourceInterface;

class Config
{
    public static $defaultConfig = array(
        'process-timeout' => 300,
        'vendor-dir'      => null,
        'releases-dir'    => 'releases',
        'max-number-release' => false
    );

    private $config;

    private $configSource;

    public function __construct()
    {
        // load defaults
        $this->config = static::$defaultConfig;
    }

    public function setConfigSource(ConfigSourceInterface $source)
    {
        $this->configSource = $source;
    }

    public function getConfigSource()
    {
        return $this->configSource;
    }

    /**
     * Merges new config values with the existing ones (overriding)
     *
     * @param array $config
     */
    public function merge(array $config)
    {
        // override defaults with given config
        if (!empty($config['config']) && is_array($config['config'])) {
            foreach ($config['config'] as $key => $val) {
                $this->config[$key] = $val;
            }
        }
    }

    /**
     * Returns a setting
     *
     * @param  string $key
     *
     * @throws \RuntimeException
     * @return mixed
     */
    public function get($key)
    {
        switch ($key) {
            case 'process-timeout':
                // convert foo-bar to OCTOWER_FOO_BAR and check if it exists since it overrides the local config
                $env = 'OCTOWER_' . strtoupper(strtr($key, '-', '_'));

                return rtrim($this->process(getenv($env) ? : $this->config[$key]), '/\\');
            default:
                if (!isset($this->config[$key])) {
                    return null;
                }

                return $this->process($this->config[$key]);
        }
    }

    public function all()
    {
        $all = array();
        foreach (array_keys($this->config) as $key) {
            $all['config'][$key] = $this->get($key);
        }

        return $all;
    }

    public function raw()
    {
        return array(
            'config' => $this->config,
        );
    }

    /**
     * Checks whether a setting exists
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Replaces {$refs} inside a config string
     *
     * @param string $value a config string that can contain {$refs-to-other-config}
     *
     * @return string
     */
    private function process($value)
    {
        $config = $this;

        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback('#\{\$(.+)\}#', function ($match) use ($config) {
            return $config->get($match[1]);
        }, $value);
    }
}