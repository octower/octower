<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Console;

use Octower\Factory;
use Octower\IO\ConsoleIO;
use Octower\IO\IOInterface;
use Octower\Octower;
use Octower\Command;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    /**
     * @var Octower
     */
    protected $octower;

    /**
    * @var IOInterface
     */
    protected $io;

    private static $logo = '
            _________
    _____  /___  ___/
  / __  /__   / /____
 / / / / __/ / / __  /
/ /_/ / /__ / / /_/ /
\____/\___//_/_____/

';

    public function __construct()
    {
        if (function_exists('ini_set')) {
            ini_set('xdebug.show_exception_trace', false);
            ini_set('xdebug.scream', false);
        }

        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }


        parent::__construct('Octower', Octower::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $output) {
            $styles = Factory::createAdditionalStyles();
            $formatter = new OutputFormatter(null, $styles);
            $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
        }

        parent::run($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->io = new ConsoleIO($input, $output, $this->getHelperSet());

        if (version_compare(PHP_VERSION, '5.3.2', '<')) {
            $output->writeln('<warning>Composer only officially supports PHP 5.3.2 and above, you will most likely encounter problems with your PHP '.PHP_VERSION.', upgrading is strongly recommended.</warning>');
        }

        if (defined('OCTOWER_DEV_WARNING_TIME') && $this->getCommandName($input) !== 'self-update' && $this->getCommandName($input) !== 'selfupdate') {
            if (time() > OCTOWER_DEV_WARNING_TIME) {
                $output->writeln(sprintf('<warning>Warning: This development build of octower is over 30 days old. It is recommended to update it by running "%s self-update" to get the latest version.</warning>', $_SERVER['PHP_SELF']));
            }
        }

        if (getenv('OCTOWER_NO_INTERACTION')) {
            $input->setInteractive(false);
        }

        if ($input->hasParameterOption('--profile')) {
            $startTime = microtime(true);
            $this->io->enableDebugging($startTime);
        }

        if ($newWorkDir = $this->getNewWorkingDir($input)) {
            $oldWorkingDir = getcwd();
            chdir($newWorkDir);
        }

        $result = parent::doRun($input, $output);

        if (isset($oldWorkingDir)) {
            chdir($oldWorkingDir);
        }

        if (isset($startTime)) {
            $output->writeln('<info>Memory usage: '.round(memory_get_usage() / 1024 / 1024, 2).'MB (peak: '.round(memory_get_peak_usage() / 1024 / 1024, 2).'MB), time: '.round(microtime(true) - $startTime, 2).'s');
        }

        return $result;
    }

    /**
     * @param  bool                    $required
     * @return \Octower\Octower
     */
    public function getOctower($required = true)
    {
        if (null === $this->octower) {
            try {
                $this->octower = Factory::create($this->io);
            } catch (\InvalidArgumentException $e) {
                if ($required) {
                    $this->io->write($e->getMessage());
                    exit(1);
                }
            }
        }

        return $this->octower;
    }

    /**
     * {@inheritDoc}
     */
    public function renderException($exception, $output)
    {
        try {
            $octower = $this->getOctower(false);
            if ($octower) {
                $config = $octower->getConfig();

                $minSpaceFree = 1024*1024;
                if ((($df = @disk_free_space($dir = $config->get('home'))) !== false && $df < $minSpaceFree)
                    || (($df = @disk_free_space($dir = $config->get('vendor-dir'))) !== false && $df < $minSpaceFree)
                ) {
                    $output->writeln('<error>The disk hosting '.$dir.' is full, this may be the cause of the following exception</error>');
                }
            }
        } catch (\Exception $e) {}

        parent::renderException($exception, $output);
    }

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * @param \Octower\IO\IOInterface $io
     *
     * @return Application
     */
    public function setIo(IOInterface $io)
    {
        $this->io = $io;
        // Rebuild octower with new IO
        $this->octower = Factory::create($this->io);

        return $this;
    }

    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\AboutCommand();
        $commands[] = new Command\SelfUpdateCommand();

        $commands[] = new Command\StatusCommand();

        $commands[] = new Command\PackageCommand();
        $commands[] = new Command\DeployCommand();
        $commands[] = new Command\ReleaseListCommand();
        $commands[] = new Command\ReleaseEnableCommand();
        $commands[] = new Command\TestCommand();

        $commands[] = new Command\Server\InitializeCommand();
        $commands[] = new Command\Server\InfoCommand();
        $commands[] = new Command\Server\PackageGetStoreCommand();
        $commands[] = new Command\Server\PackageExtractCommand();
        $commands[] = new Command\Server\ReleaseListCommand();
        $commands[] = new Command\Server\ReleaseEnableCommand();

        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information'));
        $definition->addOption(new InputOption('--working-dir', '-d', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.'));

        return $definition;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();

        $helperSet->set(new Command\Helper\DialogHelper());

        return $helperSet;
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     * @throws \RuntimeException
     */
    private function getNewWorkingDir(InputInterface $input)
    {
        $workingDir = $input->getParameterOption(array('--working-dir', '-d'));
        if (false !== $workingDir && !is_dir($workingDir)) {
            throw new \RuntimeException('Invalid working directory specified.');
        }

        return $workingDir;
    }

}