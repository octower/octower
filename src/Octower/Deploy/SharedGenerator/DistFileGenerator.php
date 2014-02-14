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

class DistFileGenerator implements GeneratorInterface
{
    public function __construct()
    {

    }

    public function generate(Filesystem $filesystem, $projectRoot, $sharedPath, $path)
    {
        $distFile = $projectRoot . DIRECTORY_SEPARATOR . $path . '.dist';

        if (!file_exists($distFile)) {
            throw new \Exception(sprintf('dist file does not exists to generate shared file %s', $path));
        }

        $filesystem->copy($distFile, $sharedPath);
    }

    public function update(Filesystem $filesystem, $projectRoot, $sharedPath, $path)
    {
        // TODO: Implement update() method.
    }
}