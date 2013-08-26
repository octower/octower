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
use Octower\Remote\SshRemote;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReleaseEnableCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('release:enable')
            ->setDescription('Enable release')
            ->setHelp(<<<EOT
<info>php octower.phar release:enable <remote> <release></info>
EOT
            )
            ->addArgument('remote', InputArgument::REQUIRED)
            ->addArgument('release', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io      = $this->getIO();

        if (!$octower->getContext() instanceof Project) {
            throw new \RuntimeException('The current context is not a project context.');
        }

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