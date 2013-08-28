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

use Octower\Deployer;
use Octower\Json\JsonFile;
use Octower\Metadata\Server;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ReleaseEnableCommand extends ServerCommand
{
    protected function configure()
    {
        $this
            ->setName('server:release:enable')
            ->setDescription('List release available on the server')
            ->setHelp(<<<EOT
<info>php octower.phar server:release:list</info>
EOT
            )
            ->addArgument('release', InputArgument::REQUIRED);
    }

    protected function doExecute(InputInterface $input)
    {
        $this->checkServerContext();

        $deployer = new Deployer($this->getIO(), $this->getOctower()->getConfig());
        $deployer->enableLocal($this->getOctower(), $this->getIO(), $input->getArgument('release'));
    }
}