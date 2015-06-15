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
use Octower\Remote\RemoteInterface;
use Symfony\Component\Console\Input\InputInterface;

class RemoteListCommand extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('remote:list')
            ->setDescription('List defined remotes')
            ->setHelp(<<<EOT
<info>%command.name%</info> list defined remotes in your octower.json file

  <info>%command.full_name%</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input)
    {
        /** @var Project $project */
        $project = $this->getOctower()->getContext();

        $this->getIO()->write('<info>Remote availables:</info>');

        foreach ($project->getRemotes() as $name => $remote) {
            /** @var RemoteInterface $remote */
            $this->getIO()->write(sprintf('    <comment>%s</comment> %s', $name, $remote->getName()));
        }
    }
}
