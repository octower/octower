<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Remote;

use Octower\IO\IOInterface;
use Octower\Metadata\Project;

interface RemoteInterface {

    public function isServerValid(IOInterface $io);

    public function getUploadDestinationFile(IOInterface $io, Project $project);

    public function sendPackage(IOInterface $io, $source, $dest);

    public function execServerCommand($cmd);

    public function override($config);
}