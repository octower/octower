<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Deploy\SharedGenerator;

use Symfony\Component\Filesystem\Filesystem;

interface GeneratorInterface
{
    public function generate(Filesystem $filesystem, $projectRoot, $sharedPath, $path);

    public function update(Filesystem $filesystem, $projectRoot, $sharedPath, $path);
} 