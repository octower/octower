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

use Octower\Json\JsonFile;
use Octower\Metadata\Server;
use Octower\Packager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ReleaseListCommand extends ServerCommand
{
    protected function configure()
    {
        $this
            ->setName('server:release:list')
            ->setDescription('List release available on the server')
            ->setHelp(<<<EOT
<info>php octower.phar server:release:list</info>
EOT
            );
    }

    protected function doExecute(InputInterface $input)
    {
        $this->checkServerContext();

        /** @var Server $server */
        $server = $this->getOctower()->getContext();

        $currentMetadata = null;
        if(file_exists(getcwd() . DIRECTORY_SEPARATOR . 'current/.octower.manifest')) {
            $currentJsonManifest = new JsonFile(getcwd() . DIRECTORY_SEPARATOR . 'current/.octower.manifest');
            $currentMetadata = $currentJsonManifest->read();
            $currentMetadata['packagedAt'] = new \DateTime($currentMetadata['packagedAt']);
        }

        // Find all releases
        $finder = new Finder();
        $finder
            ->depth(0)
            ->directories()
            ->in(getcwd() . DIRECTORY_SEPARATOR . 'releases/');

        $releases = array();

        foreach ($finder as $dir) {
            if(!file_exists($dir . DIRECTORY_SEPARATOR . '.octower.manifest')) {
                continue;
            }

            $jsonManifest = new JsonFile($dir . DIRECTORY_SEPARATOR . '.octower.manifest');
            $metadata = $jsonManifest->read();
            $metadata['packagedAt'] = new \DateTime($metadata['packagedAt']);

            $releases[] = $metadata;
        }

        usort($releases, function($r1, $r2) {
            if($r1['packagedAt'] == $r2['packagedAt']) {
                return 0;
            }

            return $r1['packagedAt'] < $r2['packagedAt'] ? -1 : 1;
        });

        $this->getIO()->write(sprintf('<info>Releases on "%s":</info>',$server->getName()));

        foreach($releases as $release) {
            if($currentMetadata && $release['version'] == $currentMetadata['version']) {
                $this->getIO()->write(sprintf(' ->  <comment>%s</comment> by %s - %s', $release['version'], str_replace(array('<', '>'), array('(', ')'), $release['author']), $release['packagedAt']->format(\DateTime::ISO8601)));
            }
            else {
                $this->getIO()->write(sprintf('     <comment>%s</comment> by %s - %s', $release['version'], str_replace(array('<', '>'), array('(', ')'), $release['author']), $release['packagedAt']->format(\DateTime::ISO8601)));
            }
        }
    }
}