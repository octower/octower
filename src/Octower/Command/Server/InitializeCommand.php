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
use Octower\IO\IOInterface;
use Octower\Json\JsonFile;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InitializeCommand extends ServerCommand
{
    protected function configure()
    {
        $this
            ->setName('server:init')
            ->setDescription('Initialize octower server')
            ->setHelp(<<<EOT
<info>php octower.phar server:init</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input)
    {
        $options = array(
            'name' => 'Octower Server',
            'type' => 'server'
        );

        $file = new JsonFile('octower.json');
        $file->write($options);

        // Initialise folders
        $fs = new Filesystem();
        $fs->mkdir(array(
            'releases',
            'shared'
        ));
    }
}