<?php



 class CSV
{


	static public function fromRecords($records)
	{
		if(!is_array($records))
		{
			throw new Exception('fromRecords expects an array');
		}
		elseif(empty($records))
		{
			return 'No data';
		}
		
		$firstRecord = $records[0];
		
		$csv = '';
		
		if(is_array($firstRecord))
		{
			$columnNames = array_keys($firstRecord);
		}
		else
		{
			$columnNames = array_map(function($field){
				return $field['columnName'];
			}, $firstRecord->getClassFields());
		}
		
		foreach($records AS $record)
		{
			// get header
			if($csv == '')
			{
				$csv .= static::rowFromArray($columnNames);
			}
			
			$csv .= static::rowFromArray(is_array($record) ? $record : $record->data);
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