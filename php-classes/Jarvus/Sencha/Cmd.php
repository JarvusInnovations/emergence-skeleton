<?php

namespace Jarvus\Sencha;

class Cmd
{
    public static $installRoot = '/usr/local/bin/Sencha/Cmd';

    protected $version;
    protected $path;


    // factories
    public static function get($version, $path = null)
    {
        if (!$path) {
            $availableVersions = static::getAvailableVersions();

            if (empty($availableVersions[$version])) {
                throw new \Exception('Could not detect path for CMD version');
            }

            $path = $availableVersions[$version];
        }

        return new static($version, $path);
    }

    public static function getLatest()
    {
        $availableVersions = static::getAvailableVersions();

        end($availableVersions);
        $latestVersion = key($availableVersions);

        return static::get($latestVersion, $availableVersions[$latestVersion]);
    }


    // magic methods and property getters
    public function __construct($version, $path)
    {
        $this->version = $version;
        $this->path = $path;
    }

    public function __toString()
    {
        return $this->path;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getPath()
    {
        return $this->path;
    }

    // public instance methods
    public function getExecutablePath()
    {
        $path = $this->getPath();

        if (substr($path, 0, 5) == '/hab/') {
            return 'hab pkg exec jarvus/sencha-cmd/'.$this->getVersion().' sencha';
        }

        return $path.'/sencha';
    }

    public function getDefaultEnv()
    {
        return [
            'SENCHA_CMD_3_0_0' => $this->getPath()
        ];
    }

    public function buildShellCommand()
    {
        $shellCommand = $this->getExecutablePath();

        $env = $this->getDefaultEnv();

        if (count($env)) {
            $shellCommand = implode(' ', array_map(function($envKey) use ($env) {
                return $envKey.'='.escapeshellarg($env[$envKey]);
            }, array_keys($env))).' '.$shellCommand;
        }

        $args = array_filter(func_get_args());
        foreach ($args AS $arg) {
            if (is_string($arg)) {
                $shellCommand .= ' '.$arg;
            } elseif (is_array($arg)) {
                $shellCommand .= ' '.implode(' ', $arg);
            }
        }

        return $shellCommand;
    }


    // static utility methods
    public static function getAvailableVersions()
    {
        $results = [];

        foreach (glob(static::$installRoot.'/*.*.*.*') AS $directory) {
            $results[basename($directory)] = $directory;
        }

        foreach (glob('/hab/pkgs/jarvus/sencha-cmd/*/*') AS $directory) {
            $results[basename(dirname($directory))] = "$directory/dist";
        }

        uksort($results, 'version_compare');

        return $results;
    }
}