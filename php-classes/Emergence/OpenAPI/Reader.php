<?php

namespace Emergence\OpenAPI;

use Exception;
use Emergence\Util\Data AS DataUtil;


class Reader
{
    public static $pathObjectProperties = [
        'get',
        'put',
        'post',
        'delete',
        'options',
        'head',
        'patch',
        'parameters'
    ];

    public static $schemaObjectProperties = [
        '$ref',
        'properties',
        'description',
        'type'
    ];


    public static function readTree(array $base = [], $root = 'api-docs')
    {
        $data = DataUtil::mergeFileTree($root, $base);

        $data['paths'] = static::findObjects(
            $data['paths'],
            [__CLASS__, 'isPathObject'],
            function (array $keys) {
                return '/' . implode('/', $keys);
            }
        );

        $data['definitions'] = static::findObjects(
            $data['definitions'],
            [__CLASS__, 'isSchemaObject'],
            function (array $keys) {
                return implode('\\', $keys);
            }
        );

        return $data;
    }

    protected static function findObjects(array $array, $sniffer, $keyMaker, array $previousKeys = [])
    {
        $results = [];

        foreach ($array AS $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $keys = array_merge($previousKeys, [$key]);

            if (call_user_func($sniffer, $value)) {
                $results[call_user_func($keyMaker, $keys)] = $value;
            } else {
                $results = array_merge($results, static::findObjects($value, $sniffer, $keyMaker, $keys));
            }
        }

        return $results;
    }

    public static function isPathObject(array $object)
    {
        foreach (static::$pathObjectProperties AS $key) {
            if (array_key_exists($key, $object)) {
                return true;
            }
        }

        return false;
    }

    public static function isSchemaObject(array $object)
    {
        foreach (static::$schemaObjectProperties AS $key) {
            if (array_key_exists($key, $object)) {
                return true;
            }
        }

        return false;
    }

    public static function dereferenceNode(array $node, array $document)
    {
        if (empty($node['$ref'])) {
            return $node;
        }

        $path = $node['$ref'];

        if ($path[0] != '#') {
            throw new Exception('Resolving remote reference is not implemented');
        }

        if ($path[1] != '/') {
            throw new Exception('Resolving relative reference is not implemented');
        }

        $pathStack = explode('/', substr($path, 2));
        $pointer = &$document;

        while (isset($pathStack[0]) && isset($pointer)) {
            $pointer = $pointer[array_shift($pathStack)];
        }

        if (is_array($pointer)) {
            $pointer['_resolvedRef'] = $path;
        }

        return $pointer;
    }

    public static function flattenDefinition(array $schema, array $document)
    {
        $schema = static::dereferenceNode($schema, $document);

        if (!empty($schema['schema'])) {
            return static::flattenDefinition($schema['schema'], $document);
        }

        if (!empty($schema['allOf'])) {
            $aggregate = [
                'properties' => [],
                'required' => []
            ];

            $definitions = array_map(function($definition) use ($document) {
                return static::dereferenceNode($definition, $document);
            }, $schema['allOf']);

            foreach ($definitions AS $definition) {
                foreach ($definition['required'] AS $required) {
                    if (!in_array($required, $aggregate['required'])) {
                        $aggregate['required'][] = $required;
                    }
                }
                unset($definition['required']);

                foreach ($definition['properties'] AS $property => $propertyData) {
                    $aggregate['properties'][$property] = $propertyData;
                }
                unset($definition['properties']);

                unset($definition['_resolvedRef']);
                $aggregate = array_merge($aggregate, $definition);
            }

            return $aggregate;
        }

        return $schema;
    }

    public static function getDefinitionIdFromPath($path)
    {
        if ($path) {
            $prefix = '#/definitions/';
            $prefixLen = strlen($prefix);

            if (substr($path, 0, $prefixLen) === $prefix) {
                return substr($path, $prefixLen);
            }
        }

        return null;
    }
}