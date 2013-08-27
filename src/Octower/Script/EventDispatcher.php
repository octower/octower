<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Script;

use Octower\IO\IOInterface;
use Octower\Octower;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * The Event Dispatcher.
 *
 * Example in command:
 *     $dispatcher = new EventDispatcher($this->getComposer(), $this->getApplication()->getIO());
 *     // ...
 *     $dispatcher->dispatch(ScriptEvents::POST_INSTALL_CMD);
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
     * @param Octower         $octower  The composer instance
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
    public function dispatch($eventName, Event $event = null)
    {
        if (null == $event) {
            $event = new Event($eventName, $this->octower, $this->io);
        }

        $this->doDispatch($event);
    }

    protected function doDispatch(Event $event)
    {
        $listeners = $this->getListeners($event);
        foreach ($listeners as $listener) {


            $listenerProcess = new Process($listener['command']);
            $listenerProcess->setTimeout(null);
            $listenerProcess->setTty(true);

            $this->io->write(sprintf('<comment>---------------------------------------</comment>
<info>Execute script "%s"</info> <notice>in %s</notice>', $listenerProcess->getCommandLine(), $listenerProcess->getWorkingDirectory()));
            $listenerProcess->run(function ($type, $data) {
                echo $data;
            });

            if (!$listenerProcess->isSuccessful()) {
                throw new \RuntimeException($listenerProcess->getErrorOutput());
            }


        }

        die();
    }

    /**
     * @param  Event $event Event object
     *
     * @return array Listeners
     */
    protected function getListeners(Event $event)
    {
        $context = $this->octower->getContext();
        $scripts = $context->getScriptsForEvent($event->getName());

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
