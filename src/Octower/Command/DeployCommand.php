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

use Octower\Deployer;
use Octower\Metadata\Project;
use Octower\Packager;
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
            ->addOption('force-version', 'fv', InputOption::VALUE_REQUIRED)
            ->addOption('generate', 'g', InputOption::VALUE_NONE)
            ->addOption('override', 'o', InputOption::VALUE_OPTIONAL, 'Override remote information for connecting');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io      = $this->getIO();

        if (!$octower->getContext() instanceof Project) {
            throw new \RuntimeException('The current context is not a project context.');
        }


        /** @var Project $project */
        $project = $octower->getContext();
        $remote = $project->getRemote($input->getArgument('remote'));

        if (!$input->getOption('generate') && strlen($input->getArgument('package')) == 0) {
            throw new InvalidArgumentException('No package provided and no --generate flag used.');
        }

        if ($input->getOption('generate') && strlen($input->getArgument('package')) > 0) {
            throw new InvalidArgumentException('Both package and --generate flag provided. Unable to determine strategy.');
        }

        if ($input->getOption('generate')) {
            $packager    = Packager::create($io, $octower);
            $packagePath = $packager->run(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR), uniqid('octower-package'));

            if ($input->getOption('force-version')) {
                $project->setVersion($input->getOption('force-version'));
            }

        } else {
            if ($input->getOption('force-version')) {
                throw new InvalidArgumentException('Unable to set a version if you choose to deploy an existing package.');
            }

            $packagePath = realpath(trim($input->getArgument('package')));
        }

        // Contact the server
        $remote->override(json_decode($input->getOption('override'), true));

        $deployer = Deployer::create($this->getIO(), $this->getOctower());
        $deployer->deploy($remote, $packagePath);


        if ($input->getOption('generate')) {
            unlink($packagePath);
        }
    }
}
