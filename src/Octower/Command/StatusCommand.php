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
use Octower\Octower;
use Octower\Packager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('project:status')
            ->setDescription('Display project status')
            ->setHelp(<<<EOT
<info>php octower.phar project:project</info>
EOT
            );
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