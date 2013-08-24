<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Command;

use Octower\Metadata\Project;
use Octower\Octower;
use Octower\Packager;
use Octower\Remote\LocalServer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\InvalidArgumentException;

class DeployCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Upload package')
            ->setHelp(<<<EOT
<info>php octower.phar deploy <remote> [<package>|--generate]</info>
EOT
            )
            ->addArgument('remote', InputArgument::REQUIRED)
            ->addArgument('package', InputArgument::OPTIONAL)
            ->addOption('generate', 'g', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io      = $this->getIO();

        if (!$octower->getContext() instanceof Project) {
            throw new \RuntimeException('The current context is not a server context.');
        }

        /** @var Project $project */
        $project = $octower->getContext();

        if (!$input->getOption('generate') && strlen($input->getArgument('package')) == 0) {
            throw new InvalidArgumentException('No package provided and no --generate flag used.');
        }

        if ($input->getOption('generate') && strlen($input->getArgument('package')) > 0) {
            throw new InvalidArgumentException('Both package and --generate flag provided. Unable to determine strategy.');
        }

        if ($input->getOption('generate')) {
            $packager    = Packager::create($io, $octower);
            $packagePath = $packager->run(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR), uniqid('octower-package'));
        } else {
            $packagePath = realpath(trim($input->getArgument('package')));
        }

        // Contact the server
        $remote = $project->getRemote($input->getArgument('remote'));
        $remote->sendPackage($io, $project, $packagePath);


        if ($input->getOption('generate')) {
            unlink($packagePath);
        }
    }
}