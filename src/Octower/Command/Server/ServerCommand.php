<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Command\Server;

use Octower\Console\Application;
use Octower\IO\BufferIO;
use Octower\IO\IOInterface;
use Octower\IO\NullIO;
use Octower\Metadata\Server;
use Octower\Octower;
use Octower\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ServerCommand extends Command
{
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->addOption('automation', null, InputOption::VALUE_NONE);
    }

    public final function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('automation')) {
            return $this->doExecute($input, $this->getIO());
        }

        $io = new BufferIO();

        $result = new \stdClass();
        $statusCode = null;

        try
        {
            $this->setIO($io);
            /** @var Application $application */
            $application = $this->getApplication();
            $application->setIo($io);

            $statusCode = $this->doExecute($input);
            $statusCode = is_numeric($statusCode) ? (int) $statusCode : 0;

            $result->statusCode = $statusCode;
            $result->output = str_replace(array('<', '>'), array('&lt;', '&gt;'), $io->getStandardOutput());
        }
        catch(\Exception $ex) {

            $result->statusCode = $ex->getCode() != 0 ? $ex->getCode() : -1;
            $result->output = $io->getStandardOutput();
            $result->exception = $ex->getMessage();
        }

        $output->write(json_encode($result), true);

        return $statusCode;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input  An InputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     * @see    setCode()
     */
    protected function doExecute(InputInterface $input)
    {
        throw new \LogicException('You must override the doExecute() method in the concrete command class.');
    }

    protected function checkServerContext()
    {
        if (!$this->getOctower()->getContext() instanceof Server) {
            throw new \RuntimeException('The current context is not a server context.');
        }
    }

}