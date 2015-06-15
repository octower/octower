<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * Based on work for Composer of :
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Script;

use Octower\IO\IOInterface;
use Octower\Metadata\Context;
use Octower\Octower;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * The Event Dispatcher.
 *     // ...
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class EventDispatcher
{
    protected $octower;
    protected $io;
    protected $loader;
    protected $process;

    /**
     * Constructor.
     *
     * @param Octower         $octower  The octower instance
     * @param IOInterface     $io       The IOInterface instance
     * @param ProcessExecutor $process
     */
    public function __construct(Octower $octower, IOInterface $io, ProcessExecutor $process = null)
    {
        $this->octower = $octower;
        $this->io      = $io;
        $this->process = $process ? : new ProcessExecutor($io);
    }

    /**
     * Dispatch a script event.
     *
     * @param string $eventName The constant in ScriptEvents
     * @param Event  $event
     */
    public function dispatch($eventName, $cwd = null, Context $additionalContext = null)
    {
        $event = new Event($eventName, $this->octower, $this->io, $cwd);
        try {
            $this->doDispatch($event, $additionalContext);
        } catch (\RuntimeException $ex) {
            throw new \Exception('An error occured during the process : ' . $ex->getMessage(), null, $ex);
        }
    }

    protected function doDispatch(Event $event, Context $additionalContext = null)
    {
        $listeners = $this->getListeners($event, $additionalContext);
        foreach ($listeners as $listener) {

            $listenerProcess = new Process($listener['command']);
            $listenerProcess->setTimeout(null);

            $listenerProcess->setWorkingDirectory($event->getCwd());
            $this->io->write(sprintf('<info>Execute script "%s"</info> <notice>in %s</notice>', $listenerProcess->getCommandLine(), $listenerProcess->getWorkingDirectory()));

            $output      = array();
            $outputError = array();

            while (!$listenerProcess->isStarted() || $listenerProcess->isRunning()) {

                if (!$listenerProcess->isStarted()) {
                    $listenerProcess->start();

                    continue;
                }

                $_output      = $listenerProcess->getIncrementalOutput();
                $_outputError = $listenerProcess->getIncrementalErrorOutput();

                if ($_output) {
                    $output[] = $_output;
                }

                if ($_outputError) {
                    $outputError[] = $_outputError;
                }
            }

            if (!$listenerProcess->isSuccessful()) {
                throw new \RuntimeException(sprintf('The script %s for the event %s has failed.', $listener['command'], $event->getName()));
            }
        }
    }

    /**
     * @param Event   $event
     * @param Context $additionalContext
     *
     * @return array|mixed
     */
    protected function getListeners(Event $event, Context $additionalContext = null)
    {
        $context = $this->octower->getContext();
        $scripts = $context->getScriptsForEvent($event->getName());

        if ($additionalContext != null) {
            $scripts = array_merge($additionalContext->getScriptsForEvent($event->getName()), $scripts);
        }

        if (empty($scripts)) {
            return array();
        }

        usort($scripts, function ($s1, $s2) {
            if ($s1['priority'] == $s2['priority']) {
                return 0;
            }

            return $s1['priority'] < $s2['priority'] ? -1 : 1;
        });


        return $scripts;
    }

    /**
     * Checks if string given references a class path and method
     *
     * @param  string $callable
     *
     * @return boolean
     */
    protected function isPhpScript($callable)
    {
        return false === strpos($callable, ' ') && false !== strpos($callable, '::');
    }
}
