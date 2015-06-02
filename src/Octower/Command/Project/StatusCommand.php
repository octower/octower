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
use Symfony\Component\Console\Input\InputInterface;

class StatusCommand extends ProjectCommand
{
    protected function configure()
    {
        $this
            ->setName('project:status')
            ->setDescription('Display project status')
            ->setHelp(<<<EOT
<info>%command.name%</info> display project status

  <info>%command.full_name%</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input, Octower $octower, IOInterface $io)
    {
        /** @var Project $project */
        $project = $octower->getContext();

        $io->write('<info>~~ Project status ~~</info>');
        $io->write('<info>Project: <comment>' . $project->getName() . '</comment></info>');
        $io->write('<info>Version: <comment>' . $project->getVersion() . '</comment></info>');

        $io->write('<info>Scripts:</info>');

        foreach(Project::getScriptEvents() as $event) {
            $scripts = $project->getScriptsByPriority($event);

            if(count($scripts) == 0) {
                continue;
            }

            $io->write(sprintf('    - <comment>%s</comment> :', $event));

            foreach($scripts as $script) {
                $io->write(sprintf('        - "%s" <notice>(priority : %s)</notice>', $script['command'], $script['priority']));
            }
        }


    }
}