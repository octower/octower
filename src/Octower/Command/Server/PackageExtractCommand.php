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
use Octower\Metadata\Server;
use Octower\Packager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PackageExtractCommand extends ServerCommand
{
    protected function configure()
    {
        $this
            ->setName('server:package:extract')
            ->setDescription('Extract a package')
            ->setHelp(<<<EOT
<info>php octower.phar server:package:extract</info>
EOT
            )
            ->addArgument('package', InputArgument::REQUIRED);
    }

    protected function doExecute(InputInterface $input)
    {
        $this->checkServerContext();

        $package = $input->getArgument('package');

        $octower = $this->getOctower();
        $io = $this->getIO();

        Packager::extract($package);
    }
}