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

use Octower\IO\IOInterface;
use Octower\Json\JsonFile;
use Octower\Script\EventDispatcher;
use Octower\Util\Filesystem;
use Octower\Util\ProcessExecutor;
use Octower\Util\RemoteFilesystem;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Factory
{
    /**
     * @param IOInterface $io IO instance
     * @param mixed $config either a configuration array or a filename to read from, if null it will read from
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
        return trim(getenv('OCTOWER')) ?: Octower::OCTOWER_FILE;
    }

    public static function getOctowerFolder()
    {
        return trim(getenv('OCTOWER_FOLDER')) ?: Octower::OCTOWER_FOLDER;
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
     * @param IOInterface $io IO instance
     * @param array|string|null $localConfig either a configuration array or a filename to read from, if null it will
     *                                       read from the default filename
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return Octower
     */
    public function createOctower(IOInterface $io, $localConfig = null)
    {
        $filesystem = new Filesystem();

        // load Composer configuration
        if (null === $localConfig) {
            $localConfig = static::getOctowerFile();
        }

        if (is_string($localConfig)) {
            $file = new JsonFile($localConfig, new RemoteFilesystem($io));
            $rootPath = basename($localConfig);
            if (!$file->exists()) {
                if ($localConfig === './octower.json' || $localConfig === 'octower.json') {
                    $message = 'Composer could not find a octower.json file in ' . getcwd();
                } else {
                    $message = 'Composer could not find the config file: ' . $localConfig;
                }
                $instructions = 'To initialize a project, please create a octower.json file as described in the http://getoctower.org/ "Getting Started" section';
                throw new \InvalidArgumentException($message . PHP_EOL . $instructions);
            }

            $file->validateSchema(JsonFile::LAX_SCHEMA);
            $localConfig = $file->read();
            $localConfig['root_path'] = $rootPath;
        }

        // Configuration defaults
        $config = static::createConfig();
        $config->merge($localConfig);
        $io->loadConfiguration($config);

        // Prepare octower folder
        $this->checkOctowerFolder($filesystem);

        // setup process timeout
        ProcessExecutor::setTimeout((int)$config->get('process-timeout'));

        // Load package metadata
        $loader = new Metadata\Loader\RootLoader($config, new ProcessExecutor($io));
        $context = $loader->load($localConfig);

        // initialize octower
        $octower = new Octower();
        $octower
            ->setConfig($config)
            ->setContext($context)
            ->setOctowerFolder($filesystem->normalizePath($this->getOctowerFolder() . DIRECTORY_SEPARATOR));

        // initialize event dispatcher
        $dispatcher = new EventDispatcher($octower, $io);
        $octower->setEventDispatcher($dispatcher);

        return $octower;
    }

    public static function createAdditionalStyles()
    {
        return array(
            'highlight' => new OutputFormatterStyle('red'),
            'warning' => new OutputFormatterStyle('black', 'yellow'),
            'notice' => new OutputFormatterStyle('cyan')
        );
    }

    protected function checkOctowerFolder(Filesystem $filesystem)
    {
        // Prepare octower folder
        if (!$filesystem->exists($this->getOctowerFolder())) {
            $filesystem->mkdir($this->getOctowerFolder());
        }
    }
}