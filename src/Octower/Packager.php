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
use Octower\Metadata\Project;
use Octower\Util\ProcessExecutor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class Packager
{
    const PACKAGE_EXTENSION = '.octopack';

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
     * @var Project
     */
    protected $project;

    protected $process;

    protected $files = array();

    public function __construct(IOInterface $io, Config $config, Project $project, ProcessExecutor $process = null)
    {
        $this->io         = $io;
        $this->config     = $config;
        $this->project    = $project;
        $this->process    = $process ? : new ProcessExecutor();
        $this->filesystem = new Filesystem();
    }


    public static function extract($package)
    {
        $filesystem = new Filesystem();

        /** @var \Phar $phar */
        $archive = new \ZipArchive();
        $archive->open($package);

        $metadata = JsonFile::parseJson($archive->getArchiveComment());

        if(!$metadata || !isset($metadata['version'])) {
            throw new \Exception('Invalid package metadata');
        }

        $releaseTarget = sprintf('releases/%s/', $metadata['version']);

        if ($filesystem->exists($releaseTarget)) {
            throw new \Exception('Release allready exist on the server');
        }

        $filesystem->mkdir($releaseTarget);

        try {
            $archive->extractTo($releaseTarget);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Create Installer
     *
     * @param  IOInterface $io
     * @param  Octower     $octower
     *
     * @throws \RuntimeException
     *
     * @return Packager
     */
    public static function create(IOInterface $io, Octower $octower)
    {
        if (!$octower->getContext() instanceof Project) {
            throw new \RuntimeException('Packager should be used in a project context only.');
        }

        /** @var Project $project */
        $project = $octower->getContext();

        return new static(
            $io,
            $octower->getConfig(),
            $project
        );
    }

    /**
     * Run package creation
     */
    public function run($buildDir = null, $packageName = null)
    {
        if ($buildDir == null) {
            $buildDir = getcwd();
        }

        $this->io->write(sprintf('<info>Project: <comment>%s</comment></info>', $this->project->getName()));
        $this->io->write(sprintf('<info>Version: <comment>%s</comment></info>', $this->project->getVersion()));

        if ($packageName == null) {
            $packageName = sprintf('%s-%s', $this->project->getNormalizedName(), $this->project->getVersion());
        }

        $packageName .= static::PACKAGE_EXTENSION;

        $this->io->write(sprintf('<info>Package Name: <comment>%s</comment></info>', $packageName));
        $this->io->write(sprintf('<info>Destination: <comment>%s</comment></info>', $buildDir));
        $this->io->write('<comment>-----------------------------------</comment>');

        if (file_exists($buildDir . DIRECTORY_SEPARATOR . $packageName)) {
            unlink($buildDir . DIRECTORY_SEPARATOR . $packageName);
        }

        $archive = new \ZipArchive();
        $archive->open($buildDir . DIRECTORY_SEPARATOR . $packageName, \ZipArchive::CREATE);

        if (null !== $this->config->get('vendor-dir')) {
            // Add vendor
            $this->detectVendors($this->config->get('vendor-dir'));

            $this->project->setExcluded(array_merge($this->project->getExcluded(), array($this->config->get('vendor-dir'))));
        }

        // Add project files
        $this->detectProjectFiles($archive);

        // Build Phar
        $this->io->write('<info>Build Package...</info>', false);
        foreach($this->files as $name => $file) {
            $archive->addFile($file, $name);
        }
        //$phar->buildFromIterator(new \ArrayIterator($this->files));
        $this->io->overwrite('<info>Build Package... <comment>Done</comment></info>');

        // Add Manifest
        $this->io->write('<info>Add Manifest...</info>', false);
        $this->addManifest($archive);
        $this->io->overwrite('<info>Add Manifest... <comment>Done</comment></info>');

        $archive->close();

        return $buildDir . DIRECTORY_SEPARATOR . $packageName;
    }

    protected function detectVendors($vendorPath = 'vendor/')
    {
        if (strpos($vendorPath, '/') === strlen($vendorPath) - 1) {
            $vendorPath = substr($vendorPath, 0, -1);
        }

        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->notName('.DS_Store')
            ->notName('*.octopack')
            ->notName('*.*~')
            ->in(getcwd() . DIRECTORY_SEPARATOR . $vendorPath);

        $i     = 0;
        $count = $finder->count();

        $this->io->write(sprintf('<info>Adding vendors files... <comment>%s/%s</comment></info>', $i, $count), false);

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */

            $i++;
            $this->io->overwrite(sprintf('<info>Adding vendors files... <comment>%s/%s</comment></info>', $i, $count), false);

            if ($file->isLink()) {
                continue;
            }

            $this->files[$vendorPath . DIRECTORY_SEPARATOR . $file->getRelativePathname()] = (string)$file;
        }

        $this->io->overwrite('<info>Adding vendors files... <comment>Done</comment></info>', true);
    }

    protected function detectProjectFiles(\ZipArchive $archive)
    {
        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->notName('.DS_Store')
            ->notName('*.octopack')
            ->notName('*.*~')
            ->in(getcwd());

        foreach ($this->project->getShared() as $path => $sharedObject) {
            if (is_dir($path)) {
                $finder->notPath($path);
            } else {
                $finder->notPath(sprintf('/%s$/', str_replace('/', '\/', $path)));
            }
        }

        foreach ($this->project->getExcluded() as $path) {
            if (is_dir($path)) {
                $finder->notPath($path);
            } else {
                $finder->notPath(sprintf('/%s$/', str_replace('/', '\/', $path)));
            }
        }

        $i     = 0;
        $count = $finder->count();

        $this->io->write(sprintf('<info>Adding project files... <comment>%s/%s</comment></info>', $i, $count), false);

        foreach ($finder as $file) {
            /** @var SplFileInfo $file */
            $i++;
            $this->io->overwrite(sprintf('<info>Adding project files... <comment>%s/%s</comment></info>', $i, $count), false);

            if ($file->isLink()) {
                continue;
            }

            $content = file_get_contents($file);

            if (strpos($content, '@package_version@') !== false) {
                $content = str_replace('@package_version@', $this->project->getVersion(), $content);
                $archive->addFromString($file->getRelativePathname(), $content);
            } else {
                $this->files[$file->getRelativePathname()] = (string)$file;
            }
        }

        $this->io->overwrite('<info>Adding project files... <comment>Done</comment></info>', true);
    }

    protected function addManifest(\ZipArchive $archive)
    {
        $file = new JsonFile('.octower.manifest');
        $author = $this->getAuthor();

        $manifest = array(
            'version'    => $this->project->getVersion(),
            'packagedAt' => date_create('now')->format(\DateTime::ISO8601),
            'author'     => sprintf('%s <%s>', $author['name'], $author['email'])
        );

        $archive->setArchiveComment($file->encode($manifest));
        $archive->addFromString('.octower.manifest', $file->encode($manifest));
    }


    protected function getAuthor()
    {
        $author = array(
            'name'  => '',
            'email' => ''
        );

        if ($this->process->execute('git config user.name', $output) == 0) {
            $author['name'] = trim($output);
        }

        if ($this->process->execute('git config user.email', $output) == 0) {
            $author['email'] = trim($output);
        }

        return $author;
    }
}