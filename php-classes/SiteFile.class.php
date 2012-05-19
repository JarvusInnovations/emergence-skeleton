<?php

class SiteFile
{
	static public $tableName = '_e_files';
	static public $dataPath = 'data';
	static public $extensionMIMETypes = array(
		'js' => 'application/javascript'
		,'php' => 'application/php'
		,'html' => 'text/html'
		,'css' => 'text/css'
	);


	private $handle;
	private $record;

	function __construct($handle, $record = null)
	{
		$this->handle = $handle;
		
		if($record)
		{
			$this->record = $record;
		}
		else
		{
			$this->record = static::createPhantom($handle);				
		}
	}
	
	
	function __get($name)
	{
		switch($name)
		{
			case 'MIMEType':
				return $this->record['Type'];
			case 'RealPath':
				return $this->getRealPath();
		}
	}
	
	
	static public function getRecordByPath($collectionID, $handle)
	{
		return DB::oneRecord(
			'SELECT * FROM `%s` WHERE CollectionID = %u AND Handle = "%s" ORDER BY ID DESC LIMIT 1'
			,array(
				static::$tableName
				,$collectionID
				,$handle
			)
		);
	}
	
	
	function getRealPath()
	{
		return static::getRealPathByID($this->record['ID']);
	}
	
	static public function getRealPathByID($ID)
	{
		return Site::$rootPath . '/' . static::$dataPath . '/' . $ID;
	}

	function getName()
	{
		return $this->handle;
	}

	function getSize()
	{
		return $this->record['Size'];
	}

	function getETag()
	{
		return $this->record['SHA1'] ? ('"'.$this->record['SHA1'].'"') : null;
	}

	function get()
	{
		return fopen($this->getRealPath(), 'r');
	}
	
	static public function create($collectionID, $handle, $data = null)
	{
		if(!$handle)
			return;
			
		$record = static::createPhantom($collectionID, $handle);
	
		if($data)
			static::saveRecordData($record, $data);
	}

	function put($data)
	{
		if($this->record['Status'] == 'Phantom' && $this->record['AuthorID'] == $GLOBALS['Session']->PersonID)
		{
			static::saveRecordData($this->record, $data);
		}
		else
		{
			$newRecord = static::createPhantom($this->record['CollectionID'], $this->handle, $this->record['ID']);
			static::saveRecordData($newRecord, $data);
		}
	}
	
	static public function createPhantom($collectionID, $handle, $ancestorID = null)
	{
		DB::nonQuery('INSERT INTO `%s` SET CollectionID = %u, Handle = "%s", Status = "Phantom", AuthorID = %u, AncestorID = %s', array(
			static::$tableName
			,$collectionID
			,DB::escape($handle)
			,$GLOBALS['Session']->PersonID
			,$ancestorID ? $ancestorID : 'NULL'
		));
		
		return array(
			'ID' => DB::insertID()
			,'CollectionID' => $collectionID
			,'Handle' => $handle
			,'Status' => 'Phantom'
			,'AuthorID' => $GLOBALS['Session']->PersonID
			,'AncestorID' => $ancestorID
		);
	}
	
	static public function saveRecordData($record, $data)
	{
		if(defined('DEBUG')) print("saveRecordData($record[ID])\n");
		
		// save file
		$filePath = static::getRealPathByID($record['ID']);
		file_put_contents($filePath, $data);
		
		// get mime type
		$mimeType = File::getMIMEType($filePath);
		
		// override MIME type by extension
		$extension = strtolower(substr(strrchr($record['Handle'], '.'), 1));
		
		if($extension && array_key_exists($extension, static::$extensionMIMETypes))
			$mimeType = static::$extensionMIMETypes[$extension];
		
		// calculate hash and update size
		DB::nonQuery('UPDATE `%s` SET SHA1 = "%s", Size = %u, Type = "%s", Status = "Normal" WHERE ID = %u', array(
			static::$tableName
			,sha1_file($filePath)
			,filesize($filePath)
			,$mimeType
			,$record['ID']
		));
	}

    public function setName($handle)
    {
    	if($this->record['Size'] == 0 && $this->record['AuthorID'] == $GLOBALS['Session']->PersonID && !$this->record['AncestorID'])
    	{
    		// updating existing record only if file is empty, by the same author, and has no ancestor
			DB::nonQuery('UPDATE `%s` SET Handle = "%s" WHERE ID = %u', array(
				static::$tableName
				,DB::escape($handle)
				,$this->record['ID']
			));
    	}
    	else
    	{
    		// clone existing record
    		DB::nonQuery(
    			'INSERT INTO `%s` SET CollectionID = %u, Handle = "%s", Status = "%s", SHA1 = "%s", Size = %u, Type = "%s", AuthorID = %u, AncestorID = %u'
    			,array(
    				static::$tableName
    				,$this->record['CollectionID']
    				,DB::escape($handle)
    				,$this->record['Status']
    				,$this->record['SHA1']
    				,$this->record['Size']
    				,$this->record['Type']
    				,$GLOBALS['Session']->PersonID
    				,$this->record['ID']
    			)	
    		);
    		$newID = DB::insertID();
    		
    		// delete current record
    		$this->delete();
    		
    		// symlink to old data point
    		symlink($this->record['ID'], static::getRealPathByID($newID));
    	}
    }

    public function delete()
    {
		DB::nonQuery('INSERT INTO `%s` SET CollectionID = %u, Handle = "%s", Status = "Deleted", AuthorID = %u, AncestorID = %u', array(
			static::$tableName
			,$this->record['CollectionID']
			,DB::escape($this->record['Handle'])
			,$GLOBALS['Session']->PersonID
			,$this->record['ID']
		));
    }
    
    
    public function outputAsResponse()
    {
		header('Content-type: '.$this->MIMEType);
		readfile($this->RealPath);
		exit();
	}
	
	
	public function getLastModified()
	{
		return strtotime($this->record['Timestamp']);
	}
	
    public function getContentType()
    {
		return $this->record['Type'];
    }

}