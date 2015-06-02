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

use Octower\Command\Command;
use Octower\IO\IOInterface;
use Octower\Metadata\Project;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ProjectCommand  extends Command
{
    public final function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkProjectContext();

        return $this->doExecute($input, $this->getOctower(), $this->getIO());
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     *
     * @param IOInterface $io
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function doExecute(InputInterface $input, IOInterface $io)
    {
        throw new \LogicException('You must override the doExecute() method in the concrete command class.');
    }

    protected function checkProjectContext()
    {
        if (!$this->getOctower()->getContext() instanceof Project) {
            throw new \RuntimeException('The current context is not a project context.');
        }
    }
}