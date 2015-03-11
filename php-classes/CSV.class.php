<?php

class CSV
{
    public static function respond($records, $filename = null, $columns = '*')
    {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment' . ($filename ? '; filename="'.str_replace('"', '', $filename).'.csv"' : ''));
        print(static::fromRecords($records, $columns));
        exit();
    }

    static public function fromRecords($records, $columns = '*')
    {
		if (!is_array($records)) {
			throw new Exception('fromRecords expects an array');
		} elseif (empty($records)) {
			return 'No data';
		}

        if (is_string($columns) && $columns != '*') {
            $columns = explode(',', $columns);
        }

		$firstRecord = $records[0];
		
		if (is_array($firstRecord)) {
			$columnNames = array_keys($firstRecord);
            $columnNames = array_combine($columnNames, $columnNames);
		} else {
            
            $dynamicFields = $firstRecord->aggregateStackedConfig('dynamicFields');
            $fields = $firstRecord->aggregateStackedConfig('fields');
            
            $columnNames = array_merge(array_keys($fields), array_keys($dynamicFields));
            $columnNames = array_combine($columnNames, $columnNames);
            
            foreach ($columnNames AS &$columnName) {
                $dynamicField = $dynamicFields[$columnName];
                $field = $fields[$columnName];
                
                if ($dynamicField && !empty($dynamicField['label'])) {
                    $columnName = $dynamicField['label'];
                } elseif($field && !empty($field['label'])) {
                    $columnName = $field['label'];
                }
            }
		}
		$csv = static::rowFromArray($columnNames, $columns);
		
		foreach ($records AS $record) {			
			$csv .= static::rowFromArray(is_array($record) ? $record : JSON::translateObjects($record, false, $columns, true), $columns);
		}
		
		return $csv;
	}

	static public function rowFromArray($array, $columns = null)
	{
        if (is_array($columns)) {
            $newArray = array();
            foreach ($columns AS $key) {
                $newArray[$key] = $array[$key];
            }
            $array = $newArray;
        }
        
		return join(',', array_map(function($value) {
			return '"'.str_replace('"', '\"', $value).'"';
		}, $array)) . "\r\n";
	}
}
