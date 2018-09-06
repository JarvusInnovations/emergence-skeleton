<?php

namespace Emergence\Exports;

use Exception;

use DB;
use Site;
use SiteFile;
use Emergence_FS;
use SpreadsheetWriter;


class ExportsRequestHandler extends \RequestHandler
{
    public static function getScripts()
    {
        static $scripts;

        if ($scripts === null) {
            $scripts = [];
            $rootCollection = 'data-exporters';
            $prefixLength = strlen($rootCollection) + 1;

            Emergence_FS::cacheTree($rootCollection);
            foreach (Emergence_FS::getTreeFiles($rootCollection) as $nodePath => $node) {
                if (basename($nodePath) == '_index.php') {
                    continue;
                }

                $path = substr($nodePath, $prefixLength, -4);

                $config = include(SiteFile::getRealPathByID($node['ID']));

                if (!is_array($config)) {
                    throw new Exception("Script $nodePath does not return array");
                }

                $config['query'] = is_callable($config['readQuery']) ? call_user_func($config['readQuery'], $_REQUEST, $config) : [];

                $scripts[$path] = $config;
            }

            ksort($scripts);
        }

        return $scripts;
    }

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Administrator');

        // execute a selected script
        $scriptPath = array_filter(static::getPath());

        if (count($scriptPath)) {
            return static::handleScriptRequest($scriptPath);
        }

        // show list of exports
        static::respond('exports', [
            'scripts' => static::getScripts()
        ]);
    }

    public static function handleScriptRequest(array $scriptPath)
    {
        // load script
        $scriptPath = implode('/', $scriptPath);
        $scriptNode = Site::resolvePath("data-exporters/{$scriptPath}.php");

        if (!$scriptNode) {
            return static::throwNotFoundError('script not found');
        }

        // load config
        $config = include($scriptNode->RealPath);

        // check config
        if (empty($config['buildRows']) || !is_callable($config['buildRows'])) {
            throw new Exception("Script $scriptPath does not have a callable buildRows method");
        }

        if (empty($config['headers']) || !is_array($config['headers'])) {
            throw new Exception("Script $scriptPath does not have a headers array");
        }

        // read query
        if (is_callable($config['readQuery'])) {
            $query = call_user_func($config['readQuery'], $_REQUEST, $config);
            ksort($query);
        } else {
            $query = [];
        }

        // read columns
        $columns = [];
        foreach ($config['headers'] as $key => $value) {
            $columns[is_int($key) ? $value : $key] = $value;
        }

        // begin reading results
        try {
            DB::suspendQueryLogging();
            set_time_limit(0);

            $results = call_user_func($config['buildRows'], $query, $config);

            // write each row
            foreach ($results as $i => $result) {
                // begin output
                if ($i == 0) {
                    header('X-Accel-Buffering: no');
                    $spreadsheetWriter = new SpreadsheetWriter([
                        'filename' => !empty($config['filename']) ? $config['filename'] : HandleBehavior::transformText($config['title'])
                    ]);
                    $spreadsheetWriter->writeRow(array_values($columns));
                    ob_end_flush();
                    flush();
                }

                // build row
                $row = [];

                foreach ($columns as $key => $header) {
                    $value = isset($result[$key]) ? $result[$key] : null;
                    $row[$header] = $value !== '' ? $value : null;
                }

                $spreadsheetWriter->writeRow($row);

                // flush results periodically
                if ($i % 10 == 0) {
                    ob_flush();
                    flush();
                }
            }
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            header('Content-Type: text/plain');
            print('Failed to export rows: '.$e->getMessage());
        } finally {
            if ($spreadsheetWriter) {
                $spreadsheetWriter->close();
            }

             DB::resumeQueryLogging();
        }

        // finish output
        exit();
    }
}