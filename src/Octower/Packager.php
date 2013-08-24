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
use Octower\Metadata\Project;
use Octower\Util\ProcessExecutor;
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
     * @var Project
     */
    protected $project;

    protected $process;

    protected $files = array();

    public function __construct(IOInterface $io, Config $config, Project $project, ProcessExecutor $process = null)
    {
        $this->io      = $io;
        $this->config  = $config;
        $this->project = $project;
        $this->process = $process ? : new ProcessExecutor();
    }

    /**
     * Create Installer
     *
     * @param  IOInterface $io
     * @param  Octower     $octower
     *
     * @return Packager
     */
    public static function create(IOInterface $io, Octower $octower)
    {
        if(!$octower->getContext() instanceof Project) {
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
        if($buildDir == null) {
            $buildDir = getcwd();
        }

        $this->io->write(sprintf('<info>Project: <comment>%s</comment></info>', $this->project->getName()));
        $this->io->write(sprintf('<info>Version: <comment>%s</comment></info>', $this->project->getVersion()));

        if($packageName == null) {
            $packageName = sprintf('%s-%s', $this->project->getNormalizedName(), $this->project->getVersion());
        }

        $packageName .= static::PACKAGE_EXTENSION;

        $this->io->write(sprintf('<info>Package Name: <comment>%s</comment></info>', $packageName));
        $this->io->write(sprintf('<info>Destination: <comment>%s</comment></info>', $buildDir));
        $this->io->write('<comment>-----------------------------------</comment>');

        if (file_exists($buildDir . DIRECTORY_SEPARATOR . $packageName)) {
            unlink($buildDir . DIRECTORY_SEPARATOR . $packageName);
        }



        $phar = new \PharData($buildDir . DIRECTORY_SEPARATOR . $packageName, 0, 'package.phar');
        $phar->setMetadata(array(
            'version' => $this->project->getVersion(),
            'packagedAt' => new \DateTime('now'),
            'author' => $this->getAuthor(),
        ));

        if (null !== $this->config->get('vendor-dir')) {
            // Add vendor
            $this->detectVendors($this->config->get('vendor-dir'));

            $this->project->setExcluded(array_merge($this->project->getExcluded(), array($this->config->get('vendor-dir'))));
        }

        // Add project files
        $this->detectProjectFiles($phar);

        // Build Phar
        $this->io->write('<info>Build Package...</info>', false);

        $phar->buildFromIterator(new \ArrayIterator($this->files));

        $this->io->write('<info>Build Package... <comment>Done</comment></info>');

        unset($phar);

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

            if (strlen($vendorPath . DIRECTORY_SEPARATOR . $file->getRelativePathname()) == 0 || strlen((string)$file) == 0) {
                var_dump('---------', $vendorPath . DIRECTORY_SEPARATOR . $file->getRelativePathname(), (string)$file, '=======');
            }

            $this->files[$vendorPath . DIRECTORY_SEPARATOR . $file->getRelativePathname()] = (string)$file;
        }

        $this->io->overwrite('<info>Adding vendors files... <comment>Done</comment></info>', true);
    }

    protected function detectProjectFiles(\PharData $phar)
    {
        $finder = new Finder();
        $finder
            ->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(false)
            ->notName('*.*~')
            ->in(getcwd());

        foreach ($this->project->getShared() as $path) {
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
                $phar->addFromString($file->getRelativePathname(), $content);
            } else {
                $this->files[$file->getRelativePathname()] = (string)$file;
            }
        }

        $this->io->overwrite('<info>Adding project files... <comment>Done</comment></info>', true);
    }

    protected function getAuthor()
    {
        $author = array(
            'name' => '',
            'email' => ''
        );

        if($this->process->execute('git config user.name', $output) == 0) {
            $author['name'] = trim($output);
        }

        if($this->process->execute('git config user.email', $output) == 0) {
            $author['email'] = trim($output);
        }

        return $author;
    }
}