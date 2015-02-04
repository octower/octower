<?php
/*
 * This file is part of Octower.
 *
 * (c) William Pottier <developer@william-pottier.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octower\Metadata;

class Release 
{
    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $author;

    /**
     * @var \DateTime
     */
    protected $packagedAt;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @param $metadata
     * @param $directory
     */
    public function __construct($metadata, $directory)
    {
        $this->version = $metadata['version'];
        $this->author = $metadata['author'];
        $this->packagedAt = $metadata['packagedAt'];
        $this->directory = $directory;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return \DateTime
     */
    public function getPackagedAt()
    {
        return $this->packagedAt;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}