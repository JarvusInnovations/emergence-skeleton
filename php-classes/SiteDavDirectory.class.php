<?php

class SiteDavDirectory extends Sabre_DAV_Directory
{
 static public $allowCreateRootCollections = true;
    
  static public $siteDirectories = array(
    '_parent' => 'ParentSiteDavDirectory'
  );

  function getChildren() {
  
    $children = array();
    
    // static directories
    foreach(static::$siteDirectories as $name => $class)
    {
      $instance = $this->getChild($name);
      
      if($instance->getName())
        $children[] = $instance;
    }
    
    // merge with root virtual collections
    return array_merge($children, VirtualDavDirectory::getAllRootCollections());
    //return VirtualDavDirectory::getAllRootCollections();
  }

    function createDirectory($name)
    {
        if(static::$allowCreateRootCollections)
        {
            return VirtualDavDirectory::getOrCreateRootCollection($name);
        }
        else
        {
             throw new Sabre_DAV_Exception_Forbidden('Creating root collections is not permitted on this site');
        }
    }
  
  function getChild($name)
  {
  
    // filter name
    $name = static::filterName($name);
  
    // check if child exists
    if(array_key_exists($name, static::$siteDirectories))
    {
      $className = static::$siteDirectories[$name];    
      return new $className($name);
    }
    elseif($collection = VirtualDavDirectory::getByHandle($name))
    {
      return $collection;
    }

    throw new Sabre_DAV_Exception_FileNotFound('The file with name: ' . $name . ' could not be found');
  }

  function childExists($name) {
    // filter name
    $name = static::filterName($name);
  
    return (boolean)$this->getChild($name);
  }

  function getName() {
    return basename(realpath('../')) . ' ('.$_SERVER['HTTP_HOST'].')';
  }
  
  static public function filterName($name)
  {
    return preg_replace('/\s*\([^)]*\)$/', '', $name);
  }
}
