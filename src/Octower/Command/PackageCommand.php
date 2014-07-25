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

use Octower\Metadata\Project;
use Octower\Packager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('package:generate')
            ->setDescription('Create package to deploy')
            ->addOption('force-version', 'fv', InputOption::VALUE_REQUIRED)
            ->setHelp(<<<EOT
<info>php octower.phar package:generate</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io = $this->getIO();

        if ($input->getOption('force-version')) {
            $context = $octower->getContext();
            if ($context instanceof Project) {
                $context->setVersion($input->getOption('force-version'));
            }
        }

        $packager = Packager::create($io, $octower);
        $packager->run();
    }
}
