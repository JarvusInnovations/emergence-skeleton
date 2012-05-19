<?php


class VirtualDavDirectory extends SiteCollection implements Sabre_DAV_ICollection, Sabre_DAV_INode
{
	static public $autoCreate = true;
	static public $fileClass = 'VirtualDavFile';


	function __construct($handle, $record = null)
	{
		try
		{
			parent::__construct($handle, $record);
		}
		catch(Exception $e)
		{
			throw new Sabre_DAV_Exception_FileNotFound($e->getMessage());
		}
	}

	// localize file creation
	public function createFile($path, $data = null)
	{
		if($this->Site != "Local")
		{
	        throw new Sabre_DAV_Exception_Forbidden('New files cannot be created under _parent');
			//return $this->getLocalizedCollection()->createFile($path, $data);
		}

		return parent::createFile($path, $data);
	}


    public function delete()
    {
    	return parent::delete();
    }

	function getChild($handle, $record = null)
	{
		if($child = parent::getChild($handle, $record))
		{
			return $child;
		}
		else
		{
			throw new Sabre_DAV_Exception_FileNotFound('The file with name: ' . $handle . ' could not be found');
		}
	}

	public function childExists($name)
	{
		try
		{
			$this->getChild($name);
			return true;
		}
		catch(Sabre_DAV_Exception_FileNotFound $e)
		{
			return false;
		}
	}

}
