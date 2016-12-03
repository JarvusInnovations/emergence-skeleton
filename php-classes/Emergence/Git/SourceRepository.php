<?php

namespace Emergence\Git;

use Site;
use Gitonomy\Git\Admin AS GitAdmin;


class SourceRepository extends \Gitonomy\Git\Repository
{
    public static $repositories = [];

    protected $id;
    protected $config;

    public static function __classLoaded()
    {
        // copy config from legacy class if not defined locally
        if (empty(static::$repositories) && class_exists('Git')) {
            static::$repositories = \Git::$repositories;
        }

        // instantiate repositories
        foreach (static::$repositories AS $id => &$repository) {
            if (is_array($repository)) {
                $repository = new static($id, $repository);
            }
        }
    }

    public function __construct($id, $config = [])
    {
        $this->id = $id;
        $this->config = $config;

        $gitDir = Site::$rootPath . '/site-data/git/' . $this->id;

        if (!is_dir($gitDir)) {
            GitAdmin::init($gitDir);
        }

        parent::__construct($gitDir);
    }
}