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

class SshServer implements ServerInterface
{
    private $config;

    /**
     * @var \Octower\Util\ProcessExecutor
     */
    private $process;

    /**
     * @var \Octower\IO\IOInterface
     */
    private $io;

    public function __construct($config, ProcessExecutor $process = null)
    {
        $this->config  = $config;
        $this->process = $process ? : new ProcessExecutor();
    }

    public function sendPackage(IOInterface $io, Project $project, $package)
    {
        $io->write(sprintf('Connecting to %s', $this->config['hostname']));

        $tryCount = 0;
        do {
            $tryCount++;

            if ($tryCount > 1) {
                $io->write('<error>Unable to login on the server.</error>');
            }

            if ($tryCount > 3) {
                $io->write('<error>3 failes login. Abort.</error>');

                return;
            }

            $connection = ssh2_connect($this->config['hostname'], $this->config['port'] ? : 22);

            $username = $io->ask('Username to connect: ');
            $password = $io->askAndHideAnswer('Password to connect: ');

        } while (false === ssh2_auth_password($connection, $username, $password));

        $io->write('connected');

        $output = $this->execSshInPath($connection, sprintf('ls -l | grep composer.phar', $project->getNormalizedName()));

        if(strlen($output) > 0) {
            throw new \RuntimeException('It seems that the remote is not a valid octower server');
        }

        $output = $this->execSshInPath($connection, sprintf('php octower.phar server:package:get-store %s', $project->getNormalizedName()));
        echo "Output: " . $output . PHP_EOL;
    }

    protected function execSshInPath($connection, $cmd)
    {
        return $this->execSsh($connection, sprintf('cd %s && %s', $this->config['path'], $cmd));
    }

    protected function execSsh($connection, $cmd)
    {
        $stream = ssh2_exec($connection, $cmd);
        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);

        return trim($output);
    }
}