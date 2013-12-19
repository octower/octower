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

class LocalRemote implements RemoteInterface
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


    public function __construct($config, ProcessExecutor $process = null)
    {
        $this->path       = $config['path'];
        $this->process    = $process ? : new ProcessExecutor();
        $this->filesystem = new Filesystem();
    }

    public function isServerValid(IOInterface $io)
    {
        $io->write(sprintf('<info>Local server path:</info> %s', $this->path));

        if ($this->process->execute('php octower.phar server:info', $output, $this->path) != 0) {
            throw new \RuntimeException('It seems that the remote is not a valid octower server');
        }

        return true;
    }

    public function getUploadDestinationFile(IOInterface $io, Project $project)
    {
        if ($this->process->execute(sprintf('php octower.phar server:package:get-store %s', $project->getNormalizedName()), $output, $this->path) != 0) {
            throw new \Exception('An error occured : ' . PHP_EOL . $output);
        }

        return trim($output);
    }

    public function sendPackage(IOInterface $io, $source, $dest)
    {
        $io->write(sprintf('<info>Remote file destination:</info> %s', $dest));
        $this->filesystem->copy($source, $dest);

        $io->write('<info>Extracting package on server...</info>', false);

        if ($this->process->execute(sprintf('php octower.phar server:package:extract %s', $dest), $output, $this->path) != 0) {
            throw new \Exception('An error occured while extracting : ' . PHP_EOL . $output);
        }

        $io->overwrite('<info>Extracting package on server...<comment>Success</comment></info>', true);
    }

    public function execServerCommand($cmd)
    {
        if ($this->process->execute(sprintf('php octower.phar %s', $cmd), $output, $this->path) != 0) {
            throw new \Exception('An error occured  : ' . PHP_EOL . $this->process->getErrorOutput());
        }

        return trim($output);
    }

    public function override($config)
    {
        if (!$config || !is_array($config)) {
            return;
        }

        if (isset($config['path'])) {
            $this->path = $config['path'];
        }
    }
}