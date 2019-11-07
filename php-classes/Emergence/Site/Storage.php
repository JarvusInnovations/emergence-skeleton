<?php

namespace Emergence\Site;

use Site;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;

class Storage
{
    protected static $filesystems;

    /**
     * Register filesystem for given bucket id
     *
     * @param string $bucketId
     * @param FilesystemInterface $fs
     */
    public static function registerFilesystem($bucketId, FilesystemInterface $fs)
    {
        static::$filesystems[$bucketId] = $fs;
    }

    /**
     * Get registered or create default local storage filesystem for given bucket id
     *
     * @param string $bucketId
     *
     * @return FilesystemInterface
     */
    public static function getFilesystem($bucketId)
    {
        if (empty(static::$filesystems[$bucketId])) {
            $adapter = new LocalAdapter(Site::$rootPath.'/site-data/'.$bucketId);
            static::$filesystems[$bucketId] = new Filesystem($adapter);
        }

        return static::$filesystems[$bucketId];
    }
}
