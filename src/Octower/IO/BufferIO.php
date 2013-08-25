<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Octower\IO;

/**
 * IOInterface that is not interactive and never writes the output
 */
class BufferIO extends BaseIO
{
    private $standardOutput;
    private $errorOutput;

    /**
     * {@inheritDoc}
     */
    public function isInteractive()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = true)
    {
        $this->standardOutput .= $messages;
        if($newline) {
            $this->standardOutput .= PHP_EOL;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function overwrite($messages, $newline = true, $size = 80)
    {
        $this->standardOutput .= PHP_EOL;
        $this->standardOutput .= $messages;
        if($newline) {
            $this->standardOutput .= PHP_EOL;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function ask($question, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function askConfirmation($question, $default = true)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function askAndValidate($question, $validator, $attempts = false, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function askAndHideAnswer($question)
    {
        return null;
    }

    public function progressStart($max, $redrawFrequency = null)
    {

    }

    public function progressAdvance($step = 1)
    {

    }

    public function progressFinnish()
    {

    }

    /**
     * @return mixed
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    /**
     * @return mixed
     */
    public function getStandardOutput()
    {
        return $this->standardOutput;
    }


}