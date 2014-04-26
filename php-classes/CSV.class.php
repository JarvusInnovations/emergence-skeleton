<?php

class CSV
{
    static public function fromRecords($records, $include = 'all')
	{
		if (!is_array($records)) {
			throw new Exception('fromRecords expects an array');
		} elseif (empty($records)) {
			return 'No data';
		}
		
		$firstRecord = $records[0];
		
		if (is_array($firstRecord)) {
			$columnNames = array_keys($firstRecord);
		} else {
			$columnNames = array_keys($firstRecord->getDetails($include, true));
            
            foreach ($columnNames AS &$columnName) {
                if (($dynamicField = $firstRecord->getStackedConfig('dynamicFields', $columnName)) && !empty($dynamicField['label'])) {
                    $columnName = $dynamicField['label'];
                } elseif(($field = $firstRecord->getStackedConfig('fields', $columnName)) && !empty($field['label'])) {
                    $columnName = $field['label'];
                }
            }
		}
    	
		$csv = static::rowFromArray($columnNames);
		
		foreach ($records AS $record) {			
			$csv .= static::rowFromArray(is_array($record) ? $record : $record->getDetails($include, true));
		}
		
		return $csv;
	}

	static public function rowFromArray($array)
	{
		return join(',', array_map(function($value) {
			return '"'.str_replace('"', '\"', $value).'"';
		}, $array)) . "\r\n";
	}
}