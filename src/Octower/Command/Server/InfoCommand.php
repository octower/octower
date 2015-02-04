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

use Octower\IO\IOInterface;
use Octower\Json\JsonFile;
use Octower\Metadata\Server;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends ServerCommand
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

    protected function doExecute(InputInterface $input)
    {
        $octower = $this->getOctower();
        $io      = $this->getIO();

        if(!$octower->getContext() instanceof Server) {
            throw new \RuntimeException('The current context is not a server context.');
        }

        /** @var Server $server */
        $server = $octower->getContext();

        $io->write(sprintf('<info>%s</info>', $server->getName()));

        $io->write('<info>Configuration:</info>');
        $config = $octower->getConfig()->all('config');
        foreach ($config['config'] as $key => $value) {
            $io->write(sprintf('    - <notice>%s</notice> : <comment>%s</comment>', $key, var_export($value, true)));
        }

    }
}