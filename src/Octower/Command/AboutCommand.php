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

use Octower\Octower;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AboutCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('about')
            ->setDescription('Short information about Octower')
            ->setHelp(<<<EOT
<info>%command.name%</info> short information about Octower

  <info>%command.full_name%</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = Octower::VERSION;
        $output->writeln(<<<EOT
<info>Octower <comment>{$version}</comment> - PHP Deployment manager</info>
<comment>Octower is an octopus.</comment>
<notice>http://getoctower.org</notice>
EOT
        );

    }
}