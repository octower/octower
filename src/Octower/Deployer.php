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
use Octower\Metadata\Loader\RootLoader;
use Octower\Metadata\Project;
use Octower\Remote\RemoteInterface;
use Octower\Remote\SshRemote;
use Octower\Script\Event;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class Deployer
{

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Project
     */
    protected $project;

    protected $files = array();

    public function __construct(IOInterface $io, Config $config, Project $project = null, ProcessExecutor $process = null)
    {
        $this->io         = $io;
        $this->config     = $config;
        $this->project    = $project;
        $this->filesystem = new Filesystem();
    }

    /**
     * Create Deployer
     *
     * @param  IOInterface $io
     * @param  Octower     $octower
     *
     * @throws \RuntimeException
     *
     * @return Deployer
     */
    public static function create(IOInterface $io, Octower $octower)
    {
        if (!$octower->getContext() instanceof Project) {
            throw new \RuntimeException('Deployer should be used in a project context only.');
        }

        /** @var Project $project */
        $project = $octower->getContext();

        return new static(
            $io,
            $octower->getConfig(),
            $project
        );
    }

    public function deploy(RemoteInterface $remote, $package)
    {
        $remote->isServerValid($this->io);

        $dest = $remote->getUploadDestinationFile($this->io, $this->project);
        $remote->sendPackage($this->io, $package, $dest);
    }

    public function enableLocal(Octower $octower, IOInterface $io, $release)
    {
        $releasePath = getcwd() . DIRECTORY_SEPARATOR . 'releases' . DIRECTORY_SEPARATOR . $release;
        $sharedPath  = getcwd() . DIRECTORY_SEPARATOR . 'shared';
        $currentPath = getcwd() . DIRECTORY_SEPARATOR . 'current';

        if(!file_exists($releasePath) || !is_dir($releasePath)) {
            throw new \Exception('Release not available');
        }

        // Load release project context to execute script
        $projectFile                = new JsonFile($releasePath . DIRECTORY_SEPARATOR . 'octower.json');
        $projectConfig              = $projectFile->read();
        $projectConfig['root_path'] = $releasePath;

        $loader = new RootLoader($octower->getConfig(), new ProcessExecutor($io));
        /** @var Project $project */
        $project = $loader->load($projectConfig);

        if (file_exists($currentPath) && readlink($currentPath) == $releasePath) {
            $this->io->write('<warning>This version is allready enabled.</warning>');
            return;
        }

        $projectFile                = new JsonFile($releasePath . DIRECTORY_SEPARATOR . 'octower.json');
        $projectConfig              = $projectFile->read();
        $projectConfig['root_path'] = $releasePath;

        $loader        = new RootLoader($octower->getConfig(), new ProcessExecutor($this->io));
        $this->project = $loader->load($projectConfig);

        $filesystem = new Filesystem();

        // Deploy Shared
        foreach ($this->project->getShared() as $shared => $sharedObject) {
            $sharedKey = md5($shared);
            if (!file_exists($sharedPath . DIRECTORY_SEPARATOR . $sharedKey)) {
                $generator = sprintf('generateShared%s', ucfirst($sharedObject['generator']));
                if (!method_exists($this, $generator)) {
                    throw new \Exception('No generator found for shared ' . $shared);
                }

                $this->$generator($filesystem, $releasePath, $sharedPath . DIRECTORY_SEPARATOR . $sharedKey, $shared);
            }

            if (is_file($sharedPath . DIRECTORY_SEPARATOR . $sharedKey)) {
                if(file_exists($releasePath . DIRECTORY_SEPARATOR . $shared)) {
                    unlink($releasePath . DIRECTORY_SEPARATOR . $shared);
                }
                symlink($sharedPath . DIRECTORY_SEPARATOR . $sharedKey, $releasePath . DIRECTORY_SEPARATOR . $shared);
            } else {
                $filesystem->symlink($sharedPath . DIRECTORY_SEPARATOR . $sharedKey, rtrim($releasePath . DIRECTORY_SEPARATOR . $shared, '/'));
            }
        }

        $octower->getEventDispatcher()->dispatch(Event::EVENT_PRE_ENABLE, $releasePath, $project);

        // Enable version
        $filesystem->symlink($releasePath, rtrim($currentPath, "/"));

        $octower->getEventDispatcher()->dispatch(Event::EVENT_POST_ENABLE, $releasePath, $project);
    }

    public function generateSharedEmptyFolder(Filesystem $filesystem, $projectRoot, $sharedPath, $path)
    {
        $filesystem->mkdir($sharedPath);
    }

    public function generateSharedDistFile(Filesystem $filesystem, $projectRoot, $sharedPath, $path)
    {
        if (!file_exists($projectRoot . DIRECTORY_SEPARATOR . $path . '.dist')) {
            throw new \Exception(sprintf('dist file does not exists to generate shared file %s', $path));
        }

        $filesystem->copy($projectRoot . DIRECTORY_SEPARATOR . $path . '.dist', $sharedPath);
    }

}