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

use Octower\Command\Command;
use Octower\Json\JsonFile;
use Octower\Metadata\Server;
use Octower\Packager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageGetStoreCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:package:get-store')
            ->setDescription('Get path to store a package')
            ->setHelp(<<<EOT
<info>php octower.phar server:package:get-store</info>
EOT
            )
            ->addArgument('name', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io      = $this->getIO();

        if (!$octower->getContext() instanceof Server) {
            throw new \RuntimeException('The current context is not a server context.');
        }

        $io->write(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid($input->hasArgument('name') ? $input->getArgument('name').'-package' : 'octower-package').Packager::PACKAGE_EXTENSION);
    }
}