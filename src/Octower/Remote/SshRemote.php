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
use Octower\Json\JsonFile;
use Octower\Metadata\Project;
use Octower\Util\ProcessExecutor;
use Octower\Ssh as Ssh;
use Seld\JsonLint\ParsingException;

class SshRemote implements RemoteInterface
{
    private $config;

    /**
     * @var \Octower\Util\ProcessExecutor
     */
    private $process;

    private $isConnected = false;

    /**
     * @var \Octower\Ssh\Configuration
     */
    private $sshConfiguration;

    /**
     * @var Ssh\Session
     */
    private $sshSession = null;

    private $sftp;

    public function __construct($config, ProcessExecutor $process = null)
    {
        $this->config = $config;
        $this->process = $process ? : new ProcessExecutor();

        $this->initialize();
    }

    public function getName()
    {
        return sprintf('ssh - %s:%s', $this->config['hostname'], $this->getPort());
    }

    public function supported()
    {
        if (!function_exists("ssh2_connect")) {
            throw new RemoteNotSupportedException('SSH remote requires you have SSH2 php extension installed. See http://www.php.net/manual/fr/book.ssh2.php');
        }
    }

    public function override($config)
    {
        if (!$config || !is_array($config)) {
            return;
        }

        $this->config = array_merge($this->config, $config);

        $this->initialize();
    }

    public function isServerValid(IOInterface $io)
    {
        $this->connect($io);

        $output = $this->execSshInPath('ls -l | grep octower.phar', $io);
        if (!strlen($output) > 0 && strpos($output, 'octower.phar') !== false) {
            throw new \RuntimeException('It seems that the remote is not a valid octower server');
        }

        $output = $this->execSshInPath('php octower.phar server:info --automation --no-ansi', $io);
        $outputJson = JsonFile::parseJson($output);
        if ($outputJson['statusCode'] != 0) {
            throw new \RuntimeException('It seems that the remote is not a valid & working octower server');
        }

        return true;
    }

    public function getUploadDestinationFile(IOInterface $io, Project $project)
    {
        $output = $this->execSshInPath(sprintf('php octower.phar server:package:get-store %s --automation --no-ansi', $project->getNormalizedName()), $io);

        $outputJson = JsonFile::parseJson($output);
        $dest = $outputJson['output'];
        $io->write('Temporary package file on the server: ' . $dest);

        return $dest;
    }

    public function sendPackage(IOInterface $io, $source, $dest)
    {
        $this->connect($io);

        $this->transfert($io, $dest, $source);

        $io->write('<info>Extracting package on server...</info>', false);

        try {
            $this->execServerCommand(sprintf('server:package:extract %s', $dest), $io);
            $io->overwrite('<info>Extracting package on server... <comment>Success</comment></info>', true);
        } catch (\RuntimeException $ex) {
            $io->overwrite('<info>Extracting package on server... <error>Failed</error></info>', true);
            $io->write(sprintf('<error>%s</error>', $ex->getMessage()), true);
        }
    }

    public function execServerCommand($cmd, IOInterface $io = null)
    {
        $output = $this->execSshInPath(sprintf('php octower.phar %s --automation --no-ansi', $cmd), $io);
        try {
            $outputJson = JsonFile::parseJson($output);

            $io->write($outputJson['output']);

            if ($outputJson['statusCode'] != 0) {
                throw new \RuntimeException($outputJson['exception']);
            }

            return $outputJson['output'];
        } catch (ParsingException $ex) {
            throw new \Exception($output);
        }
    }

    protected function initialize()
    {
        $this->sshConfiguration = new Ssh\Configuration($this->config['hostname'], $this->getPort());
    }

    protected function connect(IOInterface $io)
    {
        if ($this->sshSession) {
            return;
        }

        $io->write(sprintf('Connecting to %s (port: %s)', $this->config['hostname'], $this->getPort()));

        $tryCount = 0;
        do {
            $tryCount++;

            if ($tryCount > 1) {
                $io->write('<error>Unable to login on the server.</error>');
            }

            if ($tryCount > 3) {
                $io->write('<error>3 failed login. Abort.</error>');

                return;
            }

            $username = $io->ask('    <info>Username to connect with?</info> ');


            $io->write(array(
                '    <comment>Authentification mode:</comment>',
                '        k - public key',
                '        p - password',
                '        ? - print help'
            ));

            while (true) {
                switch ($io->ask('    <info>Your choice [k,p,?]?</info> ', '?')) {
                    case 'k':
                        $userHome = realpath($_SERVER['HOME'] . '/.ssh/id_rsa');
                        do {
                            $privateKeyFile = $io->ask(sprintf('    <info>Private key file (%s)?</info> ', $userHome), $userHome);
                        } while (!file_exists($privateKeyFile));

                        $publicKeyFile = $privateKeyFile . '.pub';
                        while (!file_exists($publicKeyFile)) {
                            $publicKeyFile = $io->ask(sprintf('    <info>Public key file (%s not found)?</info> ', $publicKeyFile), '');
                        }

                        $authentication = new Ssh\Authentication\PublicKeyFile($username, $publicKeyFile, $privateKeyFile);
                        $this->sshSession = new Ssh\Session($this->sshConfiguration, $authentication);
                        break 2;
                    case 'p':
                        $authentication = new Ssh\Authentication\Password($username, $io->askAndHideAnswer('    <info>Password to connect with?</info> '));
                        $this->sshSession = new Ssh\Session($this->sshConfiguration, $authentication);
                        break 2;
                    case '?':
                    default:
                        help:
                        $io->write(array(
                            '    k - public key',
                            '    p - password',
                        ));

                        $io->write('    ? - print help');
                        break;
                }
            }
        } while (!$this->sshSession->authenticate(false));

        $io->write('Connected to the server');
    }

    protected function transfert(IOInterface $io, $dest, $source)
    {
        $io->write('<info>Upload package on server... <comment>0%</comment></info>', false);

        // open the remote file
        if (($remoteFp = @fopen($this->sshSession->getSftp()->getUrl($dest), 'w')) === false) {
            throw new \Exception(sprintf('Could not open remote file: %s', $dest));
        }

        // open the local file
        if (($fp = fopen($source, "r")) === false) {
            throw new \Exception(sprintf('Unable to read file %s.', $source));
        }

        $filesize = filesize($source);
        $io->progressStart($filesize);
        $start = microtime(true);
        $previousPos = 0;
        $previousTick = $start;

        // send file's content
        while (!feof($fp)) {
            $chunk = fread($fp, 8192);
            fwrite($remoteFp, $chunk);

            // Update progression
            $currentPos = ftell($fp);
            $tick = microtime(true);

            $progression = ($currentPos / $filesize) * 100;
            $speed = (($currentPos - $previousPos) / ($tick - $previousTick)) / 1000;
            $remainingTime = (($filesize - $currentPos) * ($tick - $start)) / $currentPos;

            $previousPos = $currentPos;
            $previousTick = $tick;
            $io->overwrite(sprintf('<info>Upload package on server... <comment>%s%% (%s kB/s)</comment></info> - Estimated remaining time: %s', number_format($progression, 2), number_format($speed, 2), self::secondToDisplay($remainingTime)), false);
        }

        $io->overwrite(sprintf('<info>Upload package on server... <comment>Done</comment></info> - Total time: %s', self::secondToDisplay(microtime(true) - $start)));
    }

    protected function execSshInPath($cmd, IOInterface $io = null)
    {
        if ($io) {
            $io->write(sprintf('exec "%s" in <notice>%s</notice>', $cmd, $this->config['path']));
        }

        return $this->sshSession->getExec()->run(sprintf('cd %s && %s', $this->config['path'], $cmd));
    }

    protected static function secondToDisplay($time)
    {
        $temp = $time;
        $texte = array();

        if ($temp >= 3600) {
            $texte[] = sprintf('%s hours', round($temp / 3600));
            $temp = $temp % 3600;
        }

        if ($temp >= 60) {
            $texte[] = sprintf('%s minutes', round($temp / 60));
            $temp = $temp % 60;
        }

        $texte[] = sprintf('%s seconds', round($temp));

        return join(' ', $texte);
    }

    protected function getPort()
    {
        return isset($this->config['port']) ? $this->config['port'] : 22;
    }
}