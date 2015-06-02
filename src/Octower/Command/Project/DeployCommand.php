<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Command\Project;

use Octower\Deployer;
use Octower\IO\IOInterface;
use Octower\Metadata\Project;
use Octower\Octower;
use Octower\Packager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\InvalidArgumentException;

class DeployCommand extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->addArgument('remote', InputArgument::REQUIRED, 'The remote we deploy to')
            ->addArgument('package', InputArgument::OPTIONAL, 'The package file to deploy')
            ->addOption('force-version', 'fv', InputOption::VALUE_REQUIRED, 'Force the generate package version')
            ->addOption('generate', 'g', InputOption::VALUE_NONE, 'Force new package generation before deploy')
            ->addOption('override', 'o', InputOption::VALUE_OPTIONAL, 'Override remote configuration')
            ->setDescription('Upload package')
            ->setHelp(<<<EOT
<info>%command.name%</info> deploy an octower package (or a generated one) for the current version to a specified <info>remote</info>

  <info>%command.full_name% remote [package|--generate]</info>

To force the generated package to use a specific version (and not an auto-generated one) use the <info>--force-version</info> option:

  <info>%command.full_name% remote [package|--generate] --force-version=v1.0-alpha</info>
EOT
            );

    }

    protected function doExecute(InputInterface $input)
    {
        $overrideOption = $input->getOption('override', null);
        $override = null;

        if (!empty($overrideOption)) {
            $override = json_decode($overrideOption, true);

            if (!$override) {
                // Something went wrong in Json
                throw new InvalidArgumentException(sprintf('Invalid JSON on the "override" option - %s', json_last_error_msg()));
            }
        }

        if (!$input->getOption('generate') && strlen($input->getArgument('package')) == 0) {
            throw new InvalidArgumentException('No package provided and no --generate flag used.');
        }

        if ($input->getOption('generate') && strlen($input->getArgument('package')) > 0) {
            throw new InvalidArgumentException('Both package and --generate flag provided. Unable to determine strategy.');
        }

        /** @var Project $project */
        $project = $this->getOctower()->getContext();
        $remote = $project->getRemote($input->getArgument('remote'));
        $deployer = Deployer::create($this->getIO(), $this->getOctower());
        $deployer->checkRemoteSupported($remote);

        if ($input->getOption('generate')) {
            if ($input->getOption('force-version')) {
                $project->setVersion($input->getOption('force-version'));
            }

            $packager    = Packager::create($this->getIO(), $this->getOctower());
            $packagePath = $packager->run(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR), uniqid('octower-package'));

        } else {
            if ($input->getOption('force-version')) {
                throw new InvalidArgumentException('Unable to set a version if you choose to deploy an existing package.');
            }

            $packagePath = realpath(trim($input->getArgument('package')));
        }


        if ($override) {
            // override remote configuration
            $remote->override($override);
        }

        // Deploy
        $deployer->deploy($remote, $packagePath);

        if ($input->getOption('generate')) {
            unlink($packagePath);
        }
    }
}
