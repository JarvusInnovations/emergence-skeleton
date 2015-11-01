<?php

namespace Emergence\Util;

/**
 * TODO: use this in Sencha_App
 */
class Data
{
    public static function expandDottedKeysToTree($input, &$output = array())
    {
        foreach ($input AS $key => $value) {
            $keys = explode('.', $key);
            $target =& $output;

            while (count($keys) > 0) {
                $subKey = array_shift($keys);

                if (count($keys)) {
                    if (!array_key_exists($subKey, $target)) {
                        $target[$subKey] = array();
                    }

                    $target =& $target[$subKey];
                } else {
                    $target[$subKey] = $value;
                }
            }
        }

        return $output;
    }

    public static function collapseTreeToDottedKeys($input, &$output = array(), $prefix = null)
    {
        foreach ($input AS $key => $value) {
            $key = $prefix ? "$prefix.$key" : $key;
            if (is_array($value)) {
                static::collapseTreeToDottedKeys($value, $output, $key);
            } else {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Return array containing all keys in either $from or $to set on array with keys 'from' and 'to'
     */
    public static function calculateDelta($from, $to)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * return array of 'to' values from delta
     */
    public static function extractToFromDelta($delta, &$output = array())
    {
        foreach ($delta AS $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if (count($value) == 2 && array_key_exists('from', $value) && array_key_exists('to', $value)) {
                $output[$key] = $value['to'];
            } else {
                $output[$key] = static::extractToFromDelta($value);
            }
        }

        return $output;
    }
}