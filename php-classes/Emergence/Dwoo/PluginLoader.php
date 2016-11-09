<?php

namespace Emergence\Dwoo;

use Site;


class PluginLoader implements \Dwoo\ILoader
{
    public static $namespaces = array(
        'Dwoo\\Plugins\\Blocks',
        'Dwoo\\Plugins\\Filters',
        'Dwoo\\Plugins\\Functions',
        'Dwoo\\Plugins\\Helpers',
        'Dwoo\\Plugins\\Processors',
        'Emergence\\Dwoo\\Plugins'
    );

    public function loadPlugin($class, $forceRehash = true)
    {
        // TODO: cache resolution in APC and create event-handler that scans $namespaces to clear

        foreach (static::$namespaces as $namespace) {
            // check for class-based plugin
            //print("Looking for class $namespace\\$class or $namespace\\{$class}Compile<br>");
            if (class_exists("$namespace\\$class") || class_exists("$namespace\\{$class}Compile")) {
                return;
            }

            // check for function-based plugin
            $pluginPath = 'php-classes/'.str_replace('\\', '/', "$namespace\\$class");
            //print("Looking for function $pluginPath or {$pluginPath}Compile<br>");

            if (($functionNode = Site::resolvePath("{$pluginPath}.php")) || ($functionNode = Site::resolvePath("{$pluginPath}Compile.php"))) {
                //print("Loading {$functionNode->RealPath}<br>");
                include_once($functionNode->RealPath);
                return;
            }
        }

        throw new \Dwoo\Exception('Plugin "'.$class.'" could not be found in any registered plugin namespace', E_USER_NOTICE);
    }
}