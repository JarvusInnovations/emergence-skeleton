<?php


class SiteCollection
{
	static public $tableName = '_e_file_collections';
	static public $autoCreate = true;
	static public $fileClass = 'SiteFile';

	public $handle;
	public $record;

	function __construct($handle, $record = null)
	{
		$this->handle = $handle;
		
		if($record)
			$this->record = $record;
		else
			$this->record = static::getRecordByPath($handle);
			
		if(!$this->record)
		{
			if(static::$autoCreate)
				$this->record = static::createRecord($handle);
			else
				throw new Sabre_DAV_Exception_FileNotFound('Collection with name ' . $path . ' could not be located');
		}
		
	}
	
	static public function getRecordByPath($handle, $parentID = null, $siteID = null)
	{
		return DB::oneRecord(
			'SELECT * FROM `%s` WHERE SiteID = %u AND ParentID %s AND Handle = "%s" ORDER BY ID DESC LIMIT 1'
			,array(
				static::$tableName
				,$siteID ? $siteID : Site::getSiteID()
				,$parentID ? '='.$parentID : 'IS NULL'
				,$handle
			)
		);
	}

	function getChildren()
	{
		$fileClass = static::$fileClass;
		$children = array();
		
		// get collections
		$collectionResults = DB::query(
			'SELECT * FROM `%s` WHERE ParentID = %u'
			,array(
				static::$tableName
				,$this->record['ID']
			)
		);
		
		while($record = $collectionResults->fetch_assoc())
		{
			if($record['Status'] != 'Deleted')
				$children[] = new static($record['Handle'], $record);
		}

		// get files
		$fileResults = DB::query(
			'SELECT f2.* FROM (SELECT MAX(f1.ID) AS ID FROM `%1$s` f1 WHERE CollectionID = %2$u AND Status != "Phantom" GROUP BY f1.Handle) AS lastestFiles LEFT JOIN `%1$s` f2 ON (f2.ID = lastestFiles.ID)'
			,array(
				$fileClass::$tableName
				,$this->record['ID']
			)
		);
		while($record = $fileResults->fetch_assoc())
		{
			if($record['Status'] != 'Deleted')
				$children[] = new $fileClass($record['Handle'], $record);
		}

		return $children;
	}

	function getChild($handle, $record = null)
	{
		$fileClass = static::$fileClass;
		
		//print("getChild($handle)\n");
		
		// no hidden files
		if ($handle[0]=='.')
			throw new Sabre_DAV_Exception_FileNotFound('Access denied');

		// try to get collection record
		if($collection = static::getRecordByPath($handle, $this->record['ID']))
		{
			return new static($handle, $collection);
		}

		// try to get file record
		if($file = $fileClass::getRecordByPath($this->record['ID'], $handle))
		{
			if($file['Status'] == 'Deleted')
			{
				return false;
			}
		
			return new $fileClass($handle, $file);
		}
		
		
		return false;
	}
	
	public function childExists($name)
	{
		return (boolean)$this->getChild($name);
	}

	function resolvePath($path)
	{
		if(!is_array($path))
			$path = Site::splitPath($path);
			
		$node = $this;
		while($childHandle = array_shift($path))
		{
			if(is_callable(array($node,'getChild')) && $nextNode = $node->getChild($childHandle))
			{
				$node = $nextNode;
			}
			else
			{
				$node = false;
				break;
			}
		}
		
		return $node;
	}


	function getName()
	{
		return $this->handle;
	}
	
	public function createFile($path, $data = null)
	{
		if(!is_array($path))
			$path = Site::splitPath($path);
			
		$parentCollection = $this;
		
		// create collections
		while(count($path) > 1)
		{
			$parentCollection = static::getOrCreateCollection(array_shift($path), $parentCollection);
		}
	
		$fileClass = static::$fileClass;
		$fileClass::create($parentCollection->record['ID'], $path[0], $data);
	}
	
    public function createDirectory($handle)
    {
    	static::createRecord($handle, $this->record);
    }
    
    static public function getOrCreateRootCollection($handle, $siteID = null)
    {
    	return static::getOrCreateCollection($handle, null, $siteID);
    }
    
    static public function getOrCreateCollection($handle, $parentCollection = null, $siteID = null)
    {
    	if(!$siteID && $parentCollection)
    		$siteID = $parentCollection->record['SiteID'];
    
    	if(!$collection = static::getRecordByPath($handle, $parentCollection->ID, $siteID))
    	{
    		static::createRecord($handle, $parentCollection, $siteID);
    		$collection = static::getRecordByPath($handle, $parentCollection->ID, $siteID);
    	}
    
    	return new static($handle, $collection);
    }
    
    static public function createRecord($handle, $parentCollection = null, $siteID = null)
    {
		// determine new node's position
		$left = $parentCollection ? $parentCollection->record['PosRight'] : DB::oneValue('SELECT IFNULL(MAX(`PosRight`)+1,1) FROM `%s`', static::$tableName);
		$right = $left + 1;
		
		if($parentRecord)
		{
			// push rest of set right by 2 to make room
			DB::nonQuery(
				'UPDATE `%s` SET PosRight = PosRight + 2 WHERE PosRight >= %u ORDER BY PosRight DESC'
				,array(
					static::$tableName
					,$left
				)
			);
			DB::nonQuery(
				'UPDATE `%s` SET PosLeft = PosLeft + 2 WHERE PosLeft > %u ORDER BY PosLeft DESC'
				,array(
					static::$tableName
					,$left
				)
			);
		}
		
		// create record
		DB::nonQuery('INSERT INTO `%s` SET SiteID = %u, Handle = "%s", CreatorID = %u, ParentID = %s, PosLeft = %u, PosRight = %u', array(
			static::$tableName
			,$parentCollection ? $parentCollection->record['SiteID'] : ($siteID ? $siteID : Site::getSiteID())
			,DB::escape($handle)
			,$GLOBALS['Session']->PersonID
			,$parentCollection ? $parentCollection->record['ID'] : 'NULL'
			,$left
			,$right
		));
    }

    public function setName($handle)
    {
		// updating existing record only if file is empty, by the same author, and has no ancestor
		DB::nonQuery('UPDATE `%s` SET Handle = "%s" WHERE ID = %u', array(
			static::$tableName
			,DB::escape($handle)
			,$this->record['ID']
		));
    }
    
    
    
    public function getLastModified()
    {
        return time();
    }

    public function delete()
    {
    	throw new Exception('Cannot delete collection');
    }

}