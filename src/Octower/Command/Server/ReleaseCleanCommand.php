<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Command\Server;

use Octower\Json\JsonFile;
use Octower\Metadata\Release;
use Octower\Metadata\Server;
use Octower\Packager;
use Octower\ReleaseManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ReleaseCleanCommand extends ServerCommand
{
    protected function configure()
    {
        $this
            ->setName('server:release:clean')
            ->setDescription('Clean release available on the server')
            ->addOption('force', null, InputOption::VALUE_NONE, 'force the cleaning old release (don\'t ask user for confirmation)')
            ->setHelp(<<<EOT
<info>%command.full_name%</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input)
    {
        $this->checkServerContext();

        $octower = $this->getOctower();
        $io      = $this->getIO();
        $releaseManager = new ReleaseManager($io, $octower);

        $releaseManager->clean($input->getOption('force'));
    }
}