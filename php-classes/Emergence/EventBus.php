<?php

namespace Emergence;

use SiteFile;
use Cache;
use Emergence_FS;

class EventBus
{
    public static function fireEvent($event, $context, $payload = array())
    {
        $_EVENT = array(
            'event' => $event,
            'context' => $context,
            'payload' => $payload,
            'handlers' => static::getHandlers($event, $context),
            'results' => array()
        );

        foreach ($_EVENT['handlers'] AS $handlerPath => $handlerNodeID) {
            $_EVENT['currentHandlerPath'] = $handlerPath;
            $_EVENT['currentHandlerNodeID'] = $handlerNodeID;

            // create a closure for executing hanlder so that $_EMAIL is the only variable pre-defined in its scope
            $handler = function() use ($_EVENT) {
                return include(SiteFile::getRealPathByID($_EVENT['currentHandlerNodeID']));
            };

            // execute handler and save status
            $_EVENT['lastResult'] = $_EVENT['results'][$handlerPath] = $handler();
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
        
        $cacheKey = 'event-handlers:' . implode('/', $context) . '|' . $key;
        
        if ($handlers = Cache::fetch($cacheKey)) {
            return $handlers;
        }

        $contextOriginalLength = count($context);
        $handlers = array();

        while (true) {
            $contextPath = $rootCollection;

            if (count($context)) {
                $contextPath .= '/' . implode('/', $context);
            }

            if (count($context) < $contextOriginalLength) {
                $contextPath .= '/_';
            }

            $eventPath = $contextPath . '/' . $key;
            foreach (Emergence_FS::getAggregateChildren($eventPath) AS $filename => $node) {
                if ($node->Type == 'application/php') {
                    $handlers[$eventPath . '/' . $filename] = $node->ID;
                }
            }

            $eventPath = $contextPath . '/~';
            foreach (Emergence_FS::getAggregateChildren($eventPath) AS $filename => $node) {
                if ($node->Type == 'application/php') {
                    $handlers[$eventPath . '/' . $filename] = $node->ID;
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