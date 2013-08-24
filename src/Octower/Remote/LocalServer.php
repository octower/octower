<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Remote;


use Octower\IO\IOInterface;
use Octower\Metadata\Project;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;

class LocalServer implements ServerInterface
{
    private $path;

    /**
     * @var \Octower\Util\ProcessExecutor
     */
    private $process;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;


    public function __construct($path,ProcessExecutor $process = null)
    {
        $this->path    = $path;
        $this->process = $process ? : new ProcessExecutor();
        $this->filesystem = new Filesystem();
    }

    public function sendPackage(IOInterface $io, Project $project, $package)
    {
        if ($this->process->execute('php octower.phar server:info', $output, $this->path) != 0) {
            throw new \RuntimeException('It seems that the remote is not a valid octower server');
        }

        if ($this->process->execute(sprintf('php octower.phar server:package:get-store %s', $project->getNormalizedName()), $output, $this->path) != 0) {
            throw new \Exception('An error occured : ' . PHP_EOL . $output);
        }

        $dest = trim($output);
        $this->filesystem->copy($package, $dest);

        $io->write('<info>Extracting package on server...</info>', false);

        if ($this->process->execute(sprintf('php octower.phar server:package:extract %s', $dest), $output, $this->path) != 0) {
            throw new \Exception('An error occured while extracting : ' . PHP_EOL . $output);
        }

        $io->overwrite('<info>Extracting package on server...<comment>Success</comment></info>', true);
    }
}