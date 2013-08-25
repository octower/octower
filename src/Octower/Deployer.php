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
use Octower\Metadata\Project;
use Octower\Remote\SshRemote;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class Deployer {

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

    public function __construct(IOInterface $io, Config $config, Project $project, ProcessExecutor $process = null)
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
            throw new \RuntimeException('Packager should be used in a project context only.');
        }

        /** @var Project $project */
        $project = $octower->getContext();

        return new static(
            $io,
            $octower->getConfig(),
            $project
        );
    }

    /**
     * Run package creation
     */
    public function run(SshRemote $remote)
    {
        $remote->isServerValid($this->io);
    }

}