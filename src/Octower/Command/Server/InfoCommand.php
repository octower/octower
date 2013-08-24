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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:info')
            ->setDescription('Info on the octower server')
            ->setHelp(<<<EOT
<info>php octower.phar server:info</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io      = $this->getIO();

        if(!$octower->getContext() instanceof Server) {
            throw new \RuntimeException('The current context is not a server context.');
        }

        /** @var Server $server */
        $server = $octower->getContext();

        $io->write(sprintf('<info>%s</info>', $server->getName()));
    }
}