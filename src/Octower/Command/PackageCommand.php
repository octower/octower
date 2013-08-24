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
use Octower\Packager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('package:generate')
            ->setDescription('Create package to deploy')
            ->setHelp(<<<EOT
<info>php octower.phar package:generate</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io = $this->getIO();

        $packager = Packager::create($io, $octower);
        $packager->run();
    }
}