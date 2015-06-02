<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('package:test')
            ->addArgument('package', InputArgument::REQUIRED)
            ->setDescription('Test an octower package')
            ->setHelp(<<<EOT
<info>%command.name%</info> test an octower package

  <info>%command.full_name% package</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input->getArgument('package');

        $p = new \PharData($input->getArgument('package'), 0, 'package.phar');
        var_dump($p->getMetadata());

    }
}