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

use Octower\IO\IOInterface;
use Octower\Metadata\Project;
use Octower\Octower;
use Octower\Packager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class PackageCommand extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('package:generate')
            ->setDescription('Create package to deploy')
            ->addOption('force-version', 'fv', InputOption::VALUE_REQUIRED, 'Force the generate package version')
            ->addOption('name', 'n', InputOption::VALUE_REQUIRED, 'Force the name of the package')
            ->addOption('release-dir', 'r', InputOption::VALUE_REQUIRED, 'Specify where to store generate package. If omitted, the package will be generated in current working directory.')
            ->setHelp(<<<EOT
<info>%command.name%</info> create an octower package for the current version.

  <info>%command.full_name%</info>

To change the folder where the release package will be generated use the <info>--release-dir</info> option:

  <info>%command.full_name% --release-dir=releases/</info>

To force the generated package to use a specific version (and not an auto-generated one) use the <info>--force-version</info> option:

  <info>%command.full_name% --force-version=v1.0-alpha</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input)
    {
        if ($input->getOption('force-version')) {
            $context = $this->getOctower()->getContext();
            if ($context instanceof Project) {
                $context->setVersion($input->getOption('force-version'));
            }
        }

        $packager = Packager::create($this->getIO(), $this->getOctower());
        $packager->run($input->getOption('release-dir'), $input->getOption('name'));
    }
}
