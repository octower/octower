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

use Octower\IO\IOInterface;
use Octower\Metadata\Project;
use Octower\Octower;
use Octower\Remote\SshRemote;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class ReleaseEnableCommand extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('release:enable')
            ->addArgument('remote', InputArgument::REQUIRED)
            ->addArgument('release', InputArgument::REQUIRED)
            ->setDescription('Enable release on the specified remote')
            ->setHelp(<<<EOT
<info>%command.name%</info> enable release on the specified remote

  <info>%command.full_name% remote release</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input, Octower $octower, IOInterface $io)
    {
        /** @var Project $project */
        $project = $octower->getContext();

        // Contact the server
        /** @var SshRemote $remote */
        $remote = $project->getRemote($input->getArgument('remote'));
        $remote->isServerValid($io);
        $remote->execServerCommand(sprintf('server:release:enable %s', $input->getArgument('release')));

        $output = $remote->execServerCommand('server:release:list --ansi');

        $io->write(str_replace(array('&lt;', '&gt;'), array('<', '>'), $output));
    }
}