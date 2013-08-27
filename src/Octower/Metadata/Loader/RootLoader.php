<?php

/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Metadata\Loader;

use Octower\Config;
use Octower\Json\JsonFile;
use Octower\Metadata\Context;
use Octower\Metadata\Project;
use Octower\Metadata\Server;
use Octower\Remote\LocalRemote;
use Octower\Remote\SshRemote;
use Octower\Util\ProcessExecutor;

class RootLoader
{
    private $config;
    private $process;

    public function __construct(Config $config, ProcessExecutor $process = null)
    {
        $this->config  = $config;
        $this->process = $process ? : new ProcessExecutor();
    }

    /**
     * @param array $config
     *
     * @return Context
     * @throws \RuntimeException
     * @throws \UnexpectedValueException
     */
    public function load(array $config)
    {
        if (!isset($config['name'])) {
            throw new \UnexpectedValueException('Unknown project has no name defined (' . json_encode($config) . ').');
        }

        if (!isset($config['type'])) {
            throw new \UnexpectedValueException('Unknown project has no type defined (' . json_encode($config) . ').');
        }

        $method = sprintf('loadAs%s', ucfirst(strtolower($config['type'])));
        if (!method_exists($this, $method)) {
            throw new \RuntimeException(sprintf('Unable to read object of type "%s"', $config['type']));
        }

        return $this->$method($config);
    }

    /**
     * @param $config
     *
     * @return Project
     * @throws \RuntimeException
     */
    protected function loadAsProject($config)
    {
        // handle already normalized versions
        $project = new Project($config['name']);

        if (is_array($config['shared'])) {
            $project->setShared($config['shared']);
        }

        if (is_array($config['excluded'])) {
            $project->setExcluded($config['excluded']);
        }

        if ($config['remotes']) {
            foreach ($config['remotes'] as $name => $remoteConfig) {
                $remote = null;
                switch ($remoteConfig['type']) {
                    case 'ssh':
                        $remote = new SshRemote($remoteConfig['config'], $this->process);
                        break;
                    case 'local':
                        $remote = new LocalRemote($remoteConfig['config'], $this->process);
                        break;
                }

                if ($remote) {
                    $project->addRemotes($name, $remote);
                }
            }
        }

        if($config['scripts'])
        {
            foreach($config['scripts'] as $event => $scripts) {
                foreach($scripts as $script) {
                    $project->addScript($event, $script['command'], $script['priority']);
                }
            }
        }

        if (file_exists(getcwd() . DIRECTORY_SEPARATOR . '.git') && is_dir(getcwd() . DIRECTORY_SEPARATOR . '.git')) {
            if ($this->process->execute('git log --pretty="%H" -n1 HEAD', $output) != 0) {
                throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from octower git repository clone and that git binary is available.');
            }

            $project->setVersion(trim($output));

            if ($this->process->execute('git describe --tags HEAD', $output) == 0) {
                $project->setVersion(trim($output));
            }
        }
        elseif(file_exists($config['root_path'] . DIRECTORY_SEPARATOR . '.octower.manifest')) {
            $manifestFile = new JsonFile($config['root_path'] . DIRECTORY_SEPARATOR . '.octower.manifest');
            $json = $manifestFile->read();
            $project->setVersion($json['version']);
        }

        return $project;
    }

    /**
     * @param $config
     *
     * @return Server
     */
    protected function loadAsServer($config)
    {
        // handle already normalized versions
        $server = new Server($config['name']);

        return $server;
    }

}