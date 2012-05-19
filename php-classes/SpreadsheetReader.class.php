<?php

class SpreadsheetReader
{
	protected $_options = array(
		'parseHeader' => true
		,'autoTrim' => true
	);
	protected $_fh;
	protected $_columnNames;


	static public function createFromFile($filename, $options = array())
	{
		// get MIME type
		switch($mimeType = File::getMIMEType($filename))
		{
			case 'text/plain':
			case 'text/csv':
			{
				return new static(fopen($filename, 'r'), $options);
			}
			
			case 'application/vnd.ms-office':
			{
				throw new Exception('Excel import not yet supported');
			}
			
			default:
			{
				throw new Exception('Unsupported spreadsheet mime-type: '.$mimeType);
			}
		}
	
		return $mimeType;
	}
	
	
	public function __construct($fileHandle, $options = array())
	{
		$this->_fh = $fileHandle;
		$this->_options = array_merge($this->_options, $options);
		
		// read header
		if($this->_options['parseHeader'])
		{
			$this->_columnNames = $this->getNextRow();
		}
	}


	public function getNextRow($assoc = true)
	{
		if(!$row = fgetcsv($this->_fh))
		{
			return false;
		}
		
		if($this->_options['autoTrim'])
			$row = array_map('trim', $row);
	
		return $assoc&&isset($this->_columnNames) ? array_combine($this->_columnNames, $row) : $row;
	}
	
	
	public function hasColumn($columnName)
	{
		return $this->_options['parseHeader'] && in_array($columnName, $this->_columnNames);
	}

	public function hasColumns($columnNames)
	{
		return $this->_options['parseHeader'] && !array_diff($columnNames, $this->_columnNames);
	}
	
	public function getColumnNames()
	{
		return $this->_columnNames;
	}

	public function writeToTable($tableName, $type = 'MyISAM', $temporary = false)
	{
		$fieldDefs = array_map(function($cn) {
			return sprintf('`%s` varchar(255) default NULL', $cn);
		}, $this->_columnNames);
		
		// trim blank last column
		$trimLast = false;
		if(!end($this->_columnNames))
		{
			$trimLast = true;
			array_pop($fieldDefs);
		}
		
		// create table
		DB::nonQuery(
			'CREATE TABLE `%s` (%s) ENGINE=%s DEFAULT CHARSET=utf8;'
			,array(
				$tableName
				,join(',', $fieldDefs)
				,$type
			)
		);
		
		// write rows
		$count = 0;
		while($row = $this->getNextRow(false))
		{
			if($trimLast) array_pop($row);
			
			DB::nonQuery(
				'INSERT INTO `%s` VALUES ("%s")'
				,array(
					$tableName
					,implode('","', array_map(array('DB', 'escape'), $row))
				)
			);
			$count++;
		}
		
		return $count;
	}
	
	public function close()
	{
		fclose($this->_fh);
	}
}