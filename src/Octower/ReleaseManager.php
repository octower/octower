<?php
/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower;

use Octower\IO\IOInterface;
use Octower\Json\JsonFile;
use Octower\Metadata\Loader\RootLoader;
use Octower\Metadata\Release;
use Octower\Metadata\Server;
use Octower\Script\Event;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ReleaseManager
{
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Util\ProcessExecutor
     */
    protected $process;

    /**
     * @var Script\EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(IOInterface $io, Octower $octower, ProcessExecutor $process = null)
    {
        if (!$octower->getContext() instanceof Server) {
            throw new \RuntimeException('ReleaseManager should be used in a server context only.');
        }

        $this->io              = $io;
        $this->config          = $octower->getConfig();
        $this->server          = $octower->getContext();
        $this->process         = $process ? : new ProcessExecutor();
        $this->filesystem      = new Filesystem();
        $this->eventDispatcher = $octower->getEventDispatcher();
    }

    public function install($package)
    {
        // Extract package
        $releaseTarget = Packager::extract($this, $package);

        // Load release project context to execute script
        $projectFile                = new JsonFile($releaseTarget . DIRECTORY_SEPARATOR . 'octower.json');
        $projectConfig              = $projectFile->read();
        $projectConfig['root_path'] = $releaseTarget;

        $loader = new RootLoader($projectConfig, new ProcessExecutor($this->io));
        /** @var  $project */
        $project = $loader->load($projectConfig);

        $this->eventDispatcher->dispatch(Event::EVENT_POST_EXTRACT, $releaseTarget, $project);
    }

    /**
     * @return Release[]
     */
    public function all()
    {
        $finder = new Finder();
        $finder
            ->depth(0)
            ->directories()
            ->in(getcwd() . DIRECTORY_SEPARATOR . $this->config->get('releases-dir') . DIRECTORY_SEPARATOR);

        $releases = array();

        foreach ($finder as $dir) {
            if(!file_exists($dir . DIRECTORY_SEPARATOR . '.octower.manifest')) {
                continue;
            }

            $releases[] = $this->buildReleaseMetadata($dir);
        }

        usort($releases, function($r1, $r2) {
            /** @var Release $r1 */
            /** @var Release $r2 */

            if($r1->getPackagedAt() == $r2->getPackagedAt()) {
                return 0;
            }

            return $r1->getPackagedAt() < $r2->getPackagedAt() ? -1 : 1;
        });

        return $releases;
    }

    /**
     * @return Release
     */
    public function current()
    {
        $currentMetadata = null;
        $currentFolder = getcwd() . DIRECTORY_SEPARATOR . 'current';

        if(file_exists($currentFolder . DIRECTORY_SEPARATOR . '.octower.manifest')) {
            $releaseFolder = @readlink($currentFolder);
            $currentMetadata = $this->buildReleaseMetadata($releaseFolder);
        }

        return $currentMetadata;
    }

    public function clean()
    {
        $maxNumberRelease = $this->config->get('max-number-release');
        $releases = $this->all();
        $current = $this->current();

        if (!$maxNumberRelease) {
            $this->io->write('<warning>There is no rule defined to clean releases</warning>');
            return;
        }

        $currentReleaseCount = count($releases);

        if ($currentReleaseCount <= $maxNumberRelease) {
            $this->io->write(sprintf('<info>There is no release to delete (currently : %s release%s, maximum: %s)</info>', $currentReleaseCount, $currentReleaseCount > 1 ? 's' : '', $maxNumberRelease));
            return;
        }

        $numberToRemove = $currentReleaseCount - $maxNumberRelease;

        for ($i = 0; $i < $numberToRemove; $i++) {
            if ($releases[$i]->getVersion() == $current->getVersion()) {
                $numberToRemove = $i;
                break;
            }
        }

        if ($numberToRemove <= 0) {
            $this->io->write(array('<info>There is no release to delete</info>', '<comment>We cannot delete any release because there were not enough release older the current active release.</comment>'));
            return;
        }

        $this->io->write(sprintf('<info>We will remove %s release%s:</info>', $numberToRemove, $numberToRemove > 1 ? 's' : ''));

        for ($i = 0; $i < $numberToRemove; $i++) {
            $this->io->write(sprintf('    <comment>%s</comment> %s', $releases[$i]->getVersion(), $releases[$i]->getDirectory()));
        }

        if(!$this->io->askConfirmation('<warning>Are you sure? (y/N)</warning> ', false)) {
            $this->io->write('Aborting');
            return;
        }

        $filesystem = new Filesystem();
        $this->io->write(sprintf('<info>Delete release... <comment>%s/%s</comment></info>', 0, $numberToRemove), false);
        for ($i = 0; $i < $numberToRemove; $i++) {
            $this->io->overwrite(sprintf('<info>Delete release... <comment>%s/%s</comment></info>', $i+1, $numberToRemove), false);
            $filesystem->remove($releases[$i]->getDirectory());
        }

        $this->io->overwrite('<info>Delete release... <comment>Done</comment></info>', true);
    }

    public function getReleaseDirectory($version)
    {
        return sprintf('%s/%s', $this->config->get('releases-dir'), $version);
    }

    protected function buildReleaseMetadata($releaseDirectory)
    {
        $jsonManifest = new JsonFile($releaseDirectory . DIRECTORY_SEPARATOR . '.octower.manifest');
        $metadata = $jsonManifest->read();
        $metadata['packagedAt'] = new \DateTime($metadata['packagedAt']);

        return new Release($metadata, $releaseDirectory);
    }
}