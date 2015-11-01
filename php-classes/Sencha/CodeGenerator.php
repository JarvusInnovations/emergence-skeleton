<?php

namespace Sencha;

class CodeGenerator
{
    static public function getRecordModel($recordClass)
    {
        // write header
        $out = <<<EOD
/*jslint browser: true, undef: true *//*global Ext*/
Ext.define('MyApp.model.$recordClass', {
    extend: 'Ext.data.Model',
    requires: [
        'Emergence.ext.proxy.Records',
        'Ext.data.identifier.Negative'
    ],


    // model config
    idProperty: 'ID',
    identifier: 'negative',

    fields: [
EOD;


        // write fields config
        $indent = '        ';
        $firstField = true;
        foreach ($recordClass::aggregateStackedConfig('fields') AS $field => $fieldOptions) {
            $fieldConfig = static::getFieldConfig($field, $fieldOptions, $recordClass);
            
            if (!$firstField) {
                $out .= ',';
            }

            $out .= "\n$indent{";
            $firstKey = true;
            foreach ($fieldConfig AS $key => $value) {
                if (!$firstKey) {
                    $out .= ',';
                }
                
                $out .= "\n$indent    $key: " . json_encode($value);
                
                $firstKey = false;
            }
            $out .= "\n$indent}";
    
            $firstField = false;
        }
        
        
        // write footer
        $route = $recordClass::$collectionRoute ? $recordClass::$collectionRoute : '/' . str_replace(' ', '-', $recordClass::$pluralNoun);
        $out .= <<<EOD

    ],

    proxy: {
        type: 'records',
        url: '$route'
    }
});
EOD;
        
        return $out;
    }

    static public function getRecordColumns($recordClass)
    {
        
    }

    static public function getFieldConfig($field, $fieldOptions, $recordClass = null)
    {
        $fieldConfig = array(
            'name' => $field
        );

        switch ($fieldOptions['type']) {
            case 'int':
            case 'uint':
			case 'integer':
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
                $fieldConfig['type'] = 'int';
                break;

            case 'float':
            case 'double':
            case 'decimal':
                $fieldConfig['type'] = 'float';
                break;

            case 'enum':
            case 'set':
            case 'string':
            case 'clob':
            case 'blob':
            case 'password':
                $fieldConfig['type'] = 'string';
                break;
                
            case 'timestamp':
                $fieldConfig['type'] = 'date';
                $fieldConfig['dateFormat'] = 'timestamp';
                break;
            case 'date':
                $fieldConfig['type'] = 'date';
                $fieldConfig['dateFormat'] = 'Y-m-d';
                break;
            case 'year':
                $fieldConfig['type'] = 'date';
                $fieldConfig['dateFormat'] = 'Y';
                break;
                
            case 'serialized':
                $fieldConfig['type'] = 'auto';
                break;
                
            default:
                throw new \Exception("getExtTypeConfig: unhandled type $fieldOptions[type]");
        }
        
        if ($field == 'Class' && $recordClass && ($defaultClass = $recordClass::getStaticDefaultClass())) {
            $fieldConfig['defaultValue'] = $defaultClass;
        } elseif (isset($fieldOptions['default'])) {
            if ($fieldOptions['type'] == 'timestamp' && $fieldOptions['default'] == 'CURRENT_TIMESTAMP') {
                $fieldConfig['allowNull'] = true;
            } else {
                $fieldConfig['defaultValue'] = $fieldOptions['default'];
            }
        } elseif (!$fieldOptions['notnull'] || $fieldOptions['autoincrement']) {
            $fieldConfig['allowNull'] = true;
        }
        
        return $fieldConfig;
    }
}