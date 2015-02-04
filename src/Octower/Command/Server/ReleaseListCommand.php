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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ReleaseListCommand extends ServerCommand
{
    protected function configure()
    {
        $this
            ->setName('server:release:list')
            ->setDescription('List release available on the server')
            ->setHelp(<<<EOT
<info>php octower.phar server:release:list</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input)
    {
        $this->checkServerContext();

        $octower = $this->getOctower();
        $io      = $this->getIO();
        $releaseManager = new ReleaseManager($io, $octower);

        /** @var Server $server */
        $server = $this->getOctower()->getContext();

        $releases = $releaseManager->all();
        $current = $releaseManager->current();

        // Display informations
        $this->getIO()->write(sprintf('<info>Releases on "%s":</info>',$server->getName()));

        foreach ($releases as $release) {
            $this->getIO()->write(sprintf(' %s  <comment>%s</comment> by %s - %s', ($current && $release->getVersion() == $current->getVersion() ? '->' : '  '), $release->getVersion(), str_replace(array('<', '>'), array('(', ')'), $release->getAuthor()), $release->getPackagedAt()->format(\DateTime::ISO8601)));
        }
    }
}