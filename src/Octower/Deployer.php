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


use Octower\Deploy\SharedGenerator\GeneratorInterface;
use Octower\IO\IOInterface;
use Octower\Json\JsonFile;
use Octower\Metadata\Loader\RootLoader;
use Octower\Metadata\Project;
use Octower\Remote\RemoteInterface;
use Octower\Remote\SshRemote;
use Octower\Script\Event;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

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

    /**
     * @var array
     */
    protected $files = array();

    protected $deprecatedGenerators;

    protected $generators;

    protected $generatorsInstance;

    public function __construct(IOInterface $io, Config $config, Project $project = null, ProcessExecutor $process = null)
    {
        $this->io                 = $io;
        $this->config             = $config;
        $this->project            = $project;
        $this->filesystem         = new Filesystem();
        $this->generatorsInstance = array();

        $this->generators = array(
            'folder-empty'   => 'Octower\Deploy\SharedGenerator\EmptyFolderGenerator',
            'file-dist'      => 'Octower\Deploy\SharedGenerator\DistFileGenerator',
            'file-yaml-dist' => 'Octower\Deploy\SharedGenerator\DistFileYamlGenerator'
        );
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
        $releasePath   = getcwd() . DIRECTORY_SEPARATOR . 'releases' . DIRECTORY_SEPARATOR . $release;
        $sharedBaseDir = getcwd() . DIRECTORY_SEPARATOR . 'shared';
        $currentPath   = getcwd() . DIRECTORY_SEPARATOR . 'current';

        if (!file_exists($releasePath) || !is_dir($releasePath)) {
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

            return false;
        }

        $projectFile                = new JsonFile($releasePath . DIRECTORY_SEPARATOR . 'octower.json');
        $projectConfig              = $projectFile->read();
        $projectConfig['root_path'] = $releasePath;

        $loader        = new RootLoader($octower->getConfig(), new ProcessExecutor($this->io));
        $this->project = $loader->load($projectConfig);

        $filesystem = new Filesystem();

        // Deploy Shared
        foreach ($this->project->getShared() as $shared => $sharedObject) {
            try {
                $this->prepareShared($filesystem, $releasePath, $sharedBaseDir, $shared, $sharedObject);
            } catch (\Exception $ex) {
                $this->io->write(sprintf('<error>Unable to handle shared object %s : %s.</error>', $shared, $ex->getMessage()));

                return false;
            }
        }

        $octower->getEventDispatcher()->dispatch(Event::EVENT_PRE_ENABLE, $releasePath, $project);

        // Enable version
        $filesystem->symlink($releasePath, rtrim($currentPath, "/"));

        $octower->getEventDispatcher()->dispatch(Event::EVENT_POST_ENABLE, $releasePath, $project);

        return true;
    }

    public function prepareShared(Filesystem $filesystem, $releasePath, $sharedBaseDir, $shared, $sharedObject)
    {
        $sharedKey           = md5($shared);
        $sharedPath          = rtrim($sharedBaseDir . DIRECTORY_SEPARATOR . $sharedKey, DIRECTORY_SEPARATOR);
        $sharedInReleasePath = rtrim($releasePath . DIRECTORY_SEPARATOR . $shared, DIRECTORY_SEPARATOR);

        // If the shared does not exist we create it using generator
        if (!file_exists($sharedPath) ) {
            $this->getGenerator($sharedObject['generator'])->generate($filesystem, $releasePath, $sharedPath, $shared);
        } else {
            $this->getGenerator($sharedObject['generator'])->update($filesystem, $releasePath, $sharedPath, $shared);
        }

        if (file_exists($sharedInReleasePath) && (!is_link($sharedInReleasePath) || readlink($sharedInReleasePath) !== $sharedPath)) {
            // Error file or directory exist
            throw new \RuntimeException(sprintf('File, directory or other symbolic link allready exist at %s. Remove or move them to proceed to release activation.', $sharedInReleasePath));
        }

        if (is_file($sharedPath)) {
            symlink($sharedPath, $sharedInReleasePath);
        } else {
            $filesystem->symlink($sharedPath, rtrim($sharedInReleasePath, '/'));
        }
    }

    /**
     * @param $name string
     *
     * @return GeneratorInterface
     * @throws \RuntimeException
     */
    protected function getGenerator($name)
    {
        if (!isset($this->generatorsInstance[$name])) {

            $deprecated = array(
                'emptyFolder' => 'folder-empty',
                'distFile'    => 'file-dist'
            );

            // Handle old name
            if (array_key_exists($name, $deprecated)) {
                $this->io->write(sprintf('<warning>deprecated generator name "%s". Use "%s" instead.</warning>', $name, $deprecated[$name]));
                $name = $deprecated[$name];
            }

            if (!array_key_exists($name, $this->generators)) {
                throw new \RuntimeException(sprintf('Invalid generator found : %s', $name));
            }

            $this->generatorsInstance[$name] = new $this->generators[$name];
        }

        return $this->generatorsInstance[$name];
    }
}
