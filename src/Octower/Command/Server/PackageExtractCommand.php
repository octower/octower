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

use Octower\Command\Command;
use Octower\Json\JsonFile;
use Octower\Metadata\Server;
use Octower\Packager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PackageExtractCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('server:package:extract')
            ->setDescription('Extract a package')
            ->setHelp(<<<EOT
<info>php octower.phar server:package:extract</info>
EOT
            )
            ->addArgument('package', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $octower    = $this->getOctower();
        $io         = $this->getIO();
        $filesystem = new Filesystem();

        if (!$octower->getContext() instanceof Server) {
            throw new \RuntimeException('The current context is not a server context.');
        }

        $package = $input->getArgument('package');

        $phar     = new \PharData($package, 0);
        $metadata = $phar->getMetadata();

        $releaseTarget = sprintf('releases/%s/', $metadata['version'], date('Ymd-His'));

        if ($filesystem->exists($releaseTarget)) {
            throw new \Exception('Release allready exist on the server');
        }

        $filesystem->mkdir($releaseTarget);

        try {
            $phar->extractTo($releaseTarget);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}