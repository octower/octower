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
use Octower\Remote\RemoteInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoteListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('remote:list')
            ->setDescription('List defined remotes')
            ->setHelp(<<<EOT
<info>%command.name%</info> List defined remotes in your octower.json file

  <info>%command.full_name%</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower = $this->getOctower();
        $io = $this->getIO();

        /** @var Project $project */
        $project = $octower->getContext();

        $io->write('<info>Remote availables:</info>');

        foreach ($project->getRemotes() as $name => $remote) {
            /** @var RemoteInterface $remote */
            $io->write(sprintf('    <comment>%s</comment> %s', $name, $remote->getName()));
        }
    }
}
