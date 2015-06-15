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

use Octower\Command\Command;
use Octower\Json\JsonFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('project:init')
            ->setDescription('Initialize octower project')
            ->setHelp(<<<EOT
<info>php octower.phar server:init</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = array(
            'name' => 'My Octower Project',
            'type' => 'project',
            'excluded' => array(
                '.gitignore'
            ),
            'config' => array(
                'vendor-dir' => 'vendor/'
            ),
            'remotes' => array(
                'preprod' => array(
                    'type' => 'ssh',
                    'config' => array(
                        'hostname' => 'preprod.my-project.com',
                        'path' => '/var/www'
                    )
                )
            )
        );

        $file = new JsonFile('octower.json');
        $file->write($options);
    }
}