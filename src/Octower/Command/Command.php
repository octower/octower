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

use Octower\Console\Application;
use Octower\IO\IOInterface;
use Octower\IO\NullIO;
use Octower\Octower;
use Symfony\Component\Console\Command\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    /**
     * @var Octower
     */
    private $octower;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @param  bool $required
     *
     * @throws \RuntimeException
     * @return Octower
     */
    public function getOctower($required = true)
    {
        if (null === $this->octower) {
            $application = $this->getApplication();
            if ($application instanceof Application) {
                /* @var $application    Application */
                $this->octower = $application->getOctower($required);
            } elseif ($required) {
                throw new \RuntimeException(
                    'Could not create a Octower\Octower instance, you must inject ' .
                    'one if this command is not used with a Composer\Console\Application instance'
                );
            }
        }

        return $this->octower;
    }

    /**
     * @param Octower $octower
     */
    public function setOctower(Octower $octower)
    {
        $this->octower = $octower;
    }

    /**
     * @return IOInterface
     */
    public function getIO()
    {
        if (null === $this->io) {
            $application = $this->getApplication();
            if ($application instanceof Application) {
                /* @var $application    Application */
                $this->io = $application->getIO();
            } else {
                $this->io = new NullIO();
            }
        }

        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIO(IOInterface $io)
    {
        $this->io = $io;
    }
}