<?php

class SpreadsheetWriter
{
	protected $_options = array(
		'filename' => 'spreadsheet'
		,'fileHandle' => false
		,'delimiter' => ','
		,'enclosure' => '"'
		,'autoHeader' => false
	);
	protected $_fh;
	protected $_headersWritten = false;


	static public function createFromFile($filename)
	{
		// get MIME type
		switch($mimeType = File::getMIMEType($filename))
		{
			case 'text/plain':
			case 'text/csv':
			{
				return new static(fopen($filename, 'r'));
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
	
	
	public function __construct($options = array())
	{
		$this->_options = array_merge($this->_options, $options);
		$this->_fh = $this->_options['fileHandle'] ? $this->_options['fileHandle'] : fopen('php://output','w');
	}
	
	public function writeRows($rows)
	{
		foreach($rows AS $row)
		{
			$this->writeRow($row);
		}
	}
	
	public function writeHeaders()
	{
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.str_replace('"', '', $this->_options['filename']).'.csv"');
	
		return $this->_headersWritten = true;
	}

	public function writeRow($data)
	{
		if(!$this->_headersWritten)
		{
			$this->writeHeaders();
			
			if($this->_options['autoHeader'])
				$this->writeRow(array_keys($data));			
		}
	
		return fputcsv($this->_fh, $data, $this->_options['delimiter'], $this->_options['enclosure']);
	}
	
	public function close()
	{
		fclose($this->_fh);
	}
}