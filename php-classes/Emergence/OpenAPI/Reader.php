<?php

namespace Emergence\OpenAPI;

use Exception;
use ActiveRecord;
use VersionedRecord;
use RecordsRequestHandler;
use Emergence\Util\Data AS DataUtil;


class Reader
{
    public static $pathObjectProperties = [
        'x-recordsRequestHandler',
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
        'x-activeRecord',
        '$ref',
        'properties',
        'description',
        'type'
    ];


    public static function readTree(array $base = [], $root = 'api-docs')
    {
        $data = DataUtil::mergeFileTree($root, $base);


        // collapse and normalize paths
        $data['paths'] = static::findObjects(
            $data['paths'],
            [__CLASS__, 'isPathObject'],
            function (array $keys) {
                return '/' . implode('/', array_map(function ($key) {
                    return trim($key, '/');
                }, $keys));
            }
        );

        foreach ($data['paths'] as $pathKey => &$pathObject) {
            $outSubPaths = [];
            $outDefinitions = [];
            $pathObject = static::normalizePathObject($pathObject, $outSubPaths, $outDefinitions);

            foreach ($outSubPaths as $subPathKey => $subPathObject) {
                $data['paths']["{$pathKey}/{$subPathKey}"] = static::normalizePathObject($subPathObject);
            }

            foreach ($outDefinitions as $definitionKey => $definitionObject) {
                // will be normalized in next loop
                $data['definitions'][$definitionKey] = $definitionObject;
            }
        }
        ksort($data['paths']);


        // collapse and normalize definitions
        $data['definitions'] = static::findObjects(
            $data['definitions'],
            [__CLASS__, 'isSchemaObject'],
            function (array $keys) {
                return implode('\\', $keys);
            }
        );

        $data['definitions'] = array_map([__CLASS__, 'normalizeSchemaObject'], $data['definitions']);
        ksort($data['definitions']);


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

    protected static function isPathObject(array $object)
    {
        foreach (static::$pathObjectProperties AS $key) {
            if (array_key_exists($key, $object)) {
                return true;
            }
        }

        return false;
    }

    protected static function isSchemaObject(array $object)
    {
        foreach (static::$schemaObjectProperties AS $key) {
            if (array_key_exists($key, $object)) {
                return true;
            }
        }

        return false;
    }

    protected static function normalizePathObject(array $object, array &$outSubPaths = null, array &$outDefinitions = null)
    {
        if (!empty($object['x-recordsRequestHandler'])) {
            if (!isset($outSubPaths)) {
                throw new Exception('x-recordsRequestHandler value cannot be processed within a generated subpath');
            }

            if (!class_exists($object['x-recordsRequestHandler'])) {
                throw new Exception('x-recordsRequestHandler value does not match an available class: ' . $object['x-recordsRequestHandler']);
            }

            if (!is_a($object['x-recordsRequestHandler'], RecordsRequestHandler::class, true)) {
                throw new Exception('x-recordsRequestHandler value is not an RecordsRequestHandler subclass: ' . $object['x-recordsRequestHandler']);
            }

            static::fillPathsFromRecordsRequestHandler($object['x-recordsRequestHandler'], $object, $outSubPaths, $outDefinitions);

            unset($object['x-recordsRequestHandler']);
        }

        return $object;
    }

    protected static function fillPathsFromRecordsRequestHandler($className, &$outPath, array &$outSubPaths, array &$outDefinitions)
    {
        $recordClass = $className::$recordClass;
        $recordNoun = $recordClass::$singularNoun;

        // generate record definition
        $recordDefinitionName = preg_replace_callback('/(^|\s+)([a-zA-Z])/', function($matches) {
            return strtoupper($matches[2]);
        }, $recordNoun);

        $recordDefinition = [];
        static::fillSchemaFromActiveRecord($recordClass, $recordDefinition);
        $outDefinitions[$recordDefinitionName] = $recordDefinition;

        // generate response definition
        $outDefinitions["{$recordDefinitionName}Response"] = [
            'type' => 'object',
            'required' => [ 'data', 'success' ],
            'properties' => [
                'success' => [
                    'type' => 'boolean'
                ],
                'data' => [
                    'type' => 'array',
                    'items' => [ '$ref' => "#/definitions/{$recordDefinitionName}" ]
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Number of records response was limited to'
                ],
                'offset' => [
                    'type' => 'integer',
                    'description' => 'Position of first record returned in full result set'
                ],
                'total' => [
                    'type' => 'integer',
                    'description' => 'The total number of records available in the result set across all pages'
                ],
                'conditions' => [
                    'type' => 'object',
                    'description' => 'SQL filters applied to current result set '
                ]
            ]
        ];

        // GET /records
        $outPath['get'] = [
            'description' => "Get list of `{$recordClass}` record instances",
            'parameters' => [
                [ '$ref' => '#/parameters/limit' ],
                [ '$ref' => '#/parameters/offset' ],
                [ '$ref' => '#/parameters/query' ],
                [ '$ref' => '#/parameters/include' ],
                [ '$ref' => '#/parameters/format' ],
                [ '$ref' => '#/parameters/accept' ]
            ],
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                    'schema' => [
                        '$ref' => "#/definitions/{$recordDefinitionName}Response"
                    ]
                ]
            ]
        ];

        // GET /records/*fields
        $outSubPaths['*fields'] = [
            'get' => [
                'description' => "Get configuration of all available `{$recordClass}` fields",
                'parameters' => [
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'fields' => [
                                    'type' => 'object',
                                    'description' => 'All available fields and their configurations'
                                ],
                                'dynamicFields' => [
                                    'type' => 'object',
                                    'description' => 'All available dynamic fields and their configurations'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // POST /records/save
        $outSubPaths['save'] = [
            'post' => [
                'description' => "Create or update one or more `{$recordClass}` records",
                'consumes' => [ 'application/json' ],
                'parameters' => [
                    [
                        'name' => 'body',
                        'in' => 'body',
                        'description' => "Values for new `{$recordClass}` record fields",
                        'required' => true,
                        'schema' => [
                            'properties' => [
                                'data' => [
                                    'type' => 'array',
                                    'description' => 'An array of records to patch or create. Each object may omit fields to leave unchanged or use default values. Objects containing an `ID` value will patch the existing record, others will create new records.',
                                    'items' => [
                                        '$ref' => "#/definitions/{$recordDefinitionName}"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    'type' => 'array',
                                    'description' => 'A list of successfully saved records',
                                    'items' => [
                                        '$ref' => "#/definitions/{$recordDefinitionName}"
                                    ]
                                ],
                                'failed' => [
                                    'type' => 'array',
                                    'description' => 'A list of record data objects that failed to save',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'record' => [
                                                '$ref' => "#/definitions/{$recordDefinitionName}"
                                            ],
                                            'validationErrors' => [
                                                'type' => 'object',
                                                'description' => 'All validation errors from trying to save the associated record, keyed by field name'
                                            ]
                                        ]
                                    ]
                                ],
                                'message' => [
                                    'type' => 'string',
                                    'description' => 'Top line error message if save failed'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // POST /records/destroy
        $outSubPaths['destroy'] = [
            'post' => [
                'description' => "Destroy one or more `{$recordClass}` record",
                'consumes' => [ 'application/json' ],
                'parameters' => [
                    [
                        'name' => 'body',
                        'in' => 'body',
                        'description' => "List of IDs of `{$recordNoun}` records to delete",
                        'required' => true,
                        'schema' => [
                            'properties' => [
                                'data' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'ID' => [
                                                'type' => 'integer',
                                                'description' => 'Could also me an object containing the property `ID`'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    '$ref' => "#/definitions/{$recordDefinitionName}"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // GET or POST /records/create
        $outSubPaths['create'] = [
            'get' => [
                'description' => "Get form/data needed to create a `{$recordClass}` record",
                'parameters' => [
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    'type' => 'array',
                                    'items' => [
                                        '$ref' => "#/definitions/{$recordDefinitionName}"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'description' => "Create a new `{$recordClass}` record",
                'consumes' => [ 'application/x-www-form-urlencoded' ],
                'parameters' => [
                    [
                        'name' => 'body',
                        'in' => 'body',
                        'description' => "Values for new `{$recordClass}` record fields",
                        'required' => true,
                        'schema' => [ '$ref' => "#/definitions/{$recordDefinitionName}" ]
                    ],
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    '$ref' => "#/definitions/{$recordDefinitionName}"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // GET /records/{identifier}
        $outSubPaths['{identifier}'] = [
            'get' => [
                'description' => "Get an individual `{$recordClass}` record",
                'parameters' => [
                    [ '$ref' => '#/parameters/identifier' ],
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    '$ref' => "#/definitions/{$recordDefinitionName}"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // GET or POST /records/{identifier}/edit
        $outSubPaths['{identifier}/edit'] = [
            'get' => [
                'description' => "Get form/data needed to edit the `{$recordClass}` record",
                'parameters' => [
                    [ '$ref' => '#/parameters/identifier' ],
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    '$ref' => "#/definitions/{$recordDefinitionName}"
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'post' => [
                'description' => "Submit changes to apply to the `{$recordClass}` record",
                'consumes' => [ 'application/x-www-form-urlencoded' ],
                'parameters' => [
                    [
                        'name' => 'body',
                        'in' => 'body',
                        'description' => "New values for one or more `{$recordClass}` record fields",
                        'required' => true,
                        'schema' => [ '$ref' => "#/definitions/{$recordDefinitionName}" ]
                    ],
                    [ '$ref' => '#/parameters/identifier' ],
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    '$ref' => "#/definitions/{$recordDefinitionName}"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // POST /records/{identifier}/delete
        $outSubPaths['{identifier}/delete'] = [
            'post' => [
                'description' => "Delete this `{$recordClass}` record",
                'parameters' => [
                    [ '$ref' => '#/parameters/identifier' ],
                    [ '$ref' => '#/parameters/include' ],
                    [ '$ref' => '#/parameters/format' ],
                    [ '$ref' => '#/parameters/accept' ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => [
                                    'type' => 'boolean'
                                ],
                                'data' => [
                                    '$ref' => "#/definitions/{$recordDefinitionName}"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    protected static function normalizeSchemaObject(array $object)
    {
        if (!empty($object['x-activeRecord'])) {
            if (!class_exists($object['x-activeRecord'])) {
                throw new Exception('x-activeRecord value does not match an available class: ' . $object['x-activeRecord']);
            }

            if (!is_a($object['x-activeRecord'], ActiveRecord::class, true)) {
                throw new Exception('x-activeRecord value is not an ActiveRecord subclass: ' . $object['x-activeRecord']);
            }

            static::fillSchemaFromActiveRecord($object['x-activeRecord'], $object);
        }

        return $object;
    }

    protected static function fillSchemaFromActiveRecord($className, &$outSchema)
    {
        $outSchema['type'] = 'object';

        $required = [];

        foreach ($className::aggregateStackedConfig('fields') AS $fieldName => $fieldConfig) {
            if ($fieldName == 'RevisionID' && is_a($className, VersionedRecord::class, true)) {
                continue;
            }

            if ($fieldConfig['notnull'] && !isset($fieldConfig['default']) && !$fieldConfig['autoincrement']) {
                $required[] = $fieldName;
            }

            $propertyDefaults = [
                'title' => $fieldConfig['label']
            ];

            if (!empty($fieldConfig['description'])) {
                $propertyDefaults['description'] = $fieldConfig['description'];
            }

            if (isset($fieldConfig['default'])) {
                $propertyDefaults['default'] = $fieldConfig['default'];
            }

            switch ($fieldConfig['type']) {
                case 'int':
                case 'uint':
                case 'integer':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'year':
                    $propertyDefaults['type'] = 'number';
                    break;

                case 'bigint':
                    $propertyDefaults['type'] = 'number';
                    $propertyDefaults['format'] = 'int64';
                    break;

                case 'float':
                case 'decimal':
                    $propertyDefaults['type'] = 'number';
                    $propertyDefaults['format'] = 'float';
                    break;

                case 'double':
                    $propertyDefaults['type'] = 'number';
                    $propertyDefaults['format'] = 'double';
                    break;

                case 'enum':
                    $propertyDefaults['enum'] = $fieldConfig['values'];
                    // fall through to string type
                case 'set':
                case 'string':
                case 'clob':
                case 'serialized':
                case 'json':
                case 'list':
                    $propertyDefaults['type'] = 'string';
                    break;

                case 'password':
                    $propertyDefaults['type'] = 'string';
                    $propertyDefaults['format'] = 'password';
                    break;

                case 'blob':
                    $propertyDefaults['type'] = 'string';
                    $propertyDefaults['format'] = 'binary';
                    break;

                case 'boolean':
                    $propertyDefaults['type'] = 'boolean';
                    break;

                case 'timestamp':
                    $propertyDefaults['type'] = 'string';
                    $propertyDefaults['format'] = 'date-time';

                    if ($propertyDefaults['default'] == 'CURRENT_TIMESTAMP') {
                        unset($propertyDefaults['default']);

                        $description = 'Defaults to current timestamp.';
                        $propertyDefaults['description'] = !empty($propertyDefaults['description']) ? $propertyDefaults['description'] . "\n\n" . $description : $description;
                    }
                    break;

                case 'date':
                    $propertyDefaults['type'] = 'string';
                    $propertyDefaults['format'] = 'date';
                    break;
            }

            $outSchema['properties'][$fieldName] = isset($outSchema['properties'][$fieldName]) ? array_merge($propertyDefaults, $outSchema['properties'][$fieldName]) : $propertyDefaults;
        }

        // TODO: generate dynamicFields with "${desc} if `include=PhotoMedia`" descriptions

        if (count($required)) {
            $outSchema['required'] = isset($outSchema['required']) ? array_unique(array_merge($outSchema['required'], $required)) : $required;
        }
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

    public static function flattenAllRefs(array $document, array $scope = null)
    {
        // begin scope at entire document
        $scope = $scope === null ? $document : $scope;

        // loop through each direct descendent
        foreach ($scope as $key => &$value) {
            if (!is_array($value)) {
                continue;
            }

            // flatten any refs first
            $value = static::dereferenceNode($value, $document);

            // then descend to flatten any childern
            $value = static::flattenAllRefs($document, $value);
        }

        // return only the scope of this iteration
        return $scope;
    }
}