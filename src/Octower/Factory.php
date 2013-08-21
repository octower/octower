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

use Octower\Config\JsonConfigSource;
use Octower\IO\IOInterface;
use Octower\Json\JsonFile;
use Octower\Util\RemoteFilesystem;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Factory
{
    /**
     * @param IOInterface $io      IO instance
     * @param mixed       $config  either a configuration array or a filename to read from, if null it will read from
     *                             the default filename
     *
     * @return Octower
     */
    public static function create(IOInterface $io, $config = null)
    {
        /** @var Factory $factory */
        $factory = new static();

        return $factory->createOctower($io, $config);
    }

    public static function getOctowerFile()
    {
        return trim(getenv('OCTOWER')) ?: './octower.json';
    }

    /**
     * @throws \RuntimeException
     * @return Config
     */
    public static function createConfig()
    {
        $config = new Config();
        return $config;
    }

    /**
     * Creates a Octower instance
     *
     * @param IOInterface       $io          IO instance
     * @param array|string|null $localConfig either a configuration array or a filename to read from, if null it will
     *                                       read from the default filename
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return Octower
     */
    public function createOctower(IOInterface $io, $localConfig = null)
    {
        // load Composer configuration
        if (null === $localConfig) {
            $localConfig = static::getOctowerFile();
        }

        if (is_string($localConfig)) {
            $composerFile = $localConfig;
            $file = new JsonFile($localConfig, new RemoteFilesystem($io));

            if (!$file->exists()) {
                if ($localConfig === './octower.json' || $localConfig === 'octower.json') {
                    $message = 'Composer could not find a octower.json file in '.getcwd();
                } else {
                    $message = 'Composer could not find the config file: '.$localConfig;
                }
                $instructions = 'To initialize a project, please create a octower.json file as described in the http://getoctower.org/ "Getting Started" section';
                throw new \InvalidArgumentException($message.PHP_EOL.$instructions);
            }

            $file->validateSchema(JsonFile::LAX_SCHEMA);
            $localConfig = $file->read();
        }

        // Configuration defaults
        $config = static::createConfig();
        $config->merge($localConfig);
        $io->loadConfiguration($config);

        return null;
    }

    public static function createAdditionalStyles()
    {
        return array(
            'highlight' => new OutputFormatterStyle('red'),
            'warning'   => new OutputFormatterStyle('black', 'yellow'),
        );
    }
}