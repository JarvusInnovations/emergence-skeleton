<?php

namespace Emergence;

use Cache;
use Emergence_FS;
use SiteFile;

class EventBus
{
    public static function fireEvent($name, $context, $payload = [])
    {
        $_EVENT = array_merge($payload, [
            'NAME' => $name,
            'CONTEXT' => $context,
            'HANDLERS' => static::getHandlers($name, $context),
            'RESULTS' => []
        ]);

        foreach ($_EVENT['HANDLERS'] as $handlerPath => $handlerNodeID) {
            $_EVENT['CURRENT_HANDLER_PATH'] = $handlerPath;
            $_EVENT['CURRENT_HANDLER_ID'] = $handlerNodeID;

            // create a closure for executing hanlder so that $_EMAIL is the only variable pre-defined in its scope
            $handler = function () use (&$_EVENT) {
                return include(SiteFile::getRealPathByID($_EVENT['CURRENT_HANDLER_ID']));
            };

            // execute handler and save status
            $_EVENT['LAST_RESULT'] = $_EVENT['RESULTS'][$handlerPath] = $handler();
        }

        return $_EVENT;
    }

    /**
     * TODO: merge this upstream to somewhere generic? Compare with mail handler? Compare with site-root handler?
     */
    public static function getHandlers($key, $context, $rootCollection = 'event-handlers')
    {
        if (is_string($context)) {
            $context = preg_split('{[\\\\\\/]}', $context);
        }

        $cacheKey = 'event-handlers:'.implode('/', $context).'|'.$key;

        if ($handlers = Cache::fetch($cacheKey)) {
            return $handlers;
        }

        $contextOriginalLength = count($context);
        $handlers = [];
        Emergence_FS::cacheTree($rootCollection);

        while (true) {
            $contextPath = $rootCollection;

            if (count($context)) {
                $contextPath .= '/'.implode('/', $context);
            }

            if (count($context) < $contextOriginalLength) {
                $contextPath .= '/_';
            }

            $eventPath = $contextPath.'/'.$key;
            $handlerNodes = Emergence_FS::getAggregateChildren($eventPath);
            ksort($handlerNodes);
            foreach ($handlerNodes as $filename => $node) {
                if ($node->Type == 'application/php') {
                    $handlers[$eventPath.'/'.$filename] = $node->ID;
                }
            }

            $eventPath = $contextPath.'/~';
            $handlerNodes = Emergence_FS::getAggregateChildren($eventPath);
            ksort($handlerNodes);
            foreach ($handlerNodes as $filename => $node) {
                if ($node->Type == 'application/php') {
                    $handlers[$eventPath.'/'.$filename] = $node->ID;
                }
            }

            if (count($context)) {
                array_pop($context);
            } else {
                break;
            }
        }

        Cache::store($cacheKey, $handlers);

        return $handlers;
    }
}
