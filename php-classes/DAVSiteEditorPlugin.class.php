<?php
/*
Sabre_DAV_FS_Directory�

Directory class

    * Parent class: Sabre_DAV_FS_Node
    * Implements: Sabre_DAV_INode, Sabre_DAV_ICollection, Sabre_DAV_IQuota 

Properties�
path�

protected Sabre_DAV_FS_Node::$path

The path to the current node

    * This property is protected. 

Methods�
createFile�

void Sabre_DAV_FS_Directory::createFile(string $name, resource $data = NULL)

Creates a new file in the directory

data is a readable stream resource
createDirectory�

void Sabre_DAV_FS_Directory::createDirectory(string $name)

Creates a new subdirectory
getChild

Sabre_DAV_INode Sabre_DAV_FS_Directory::getChild(string $name)

Returns a specific child node, referenced by its name
getChildren

Sabre_DAV_INode[] Sabre_DAV_FS_Directory::getChildren()

Returns an array with all the child nodes
childExists

bool Sabre_DAV_FS_Directory::childExists(string $name)

Checks if a child exists.
delete

void Sabre_DAV_FS_Directory::delete()

Deletes all files in this directory, and then itself
getQuotaInfo

array Sabre_DAV_FS_Directory::getQuotaInfo()

Returns available diskspace information
__construct�

void Sabre_DAV_FS_Node::__construct(string $path)

Sets up the node, expects a full path name

    * Defined in Sabre_DAV_FS_Node 

getName�

string Sabre_DAV_FS_Node::getName()

Returns the name of the node

    * Defined in Sabre_DAV_FS_Node 

setName�

void Sabre_DAV_FS_Node::setName(string $name)

Renames the node

    * Defined in Sabre_DAV_FS_Node 

getLastModified�

int Sabre_DAV_FS_Node::getLastModified()

Returns the last modification time, as a unix timestamp

    * Defined in Sabre_DAV_FS_Node 
 */


class DAVSiteEditorPlugin extends Sabre_DAV_Browser_Plugin
{
    public function generateDirectoryIndex($path) {
        
        $data['path'] = $path;
        
        $pathParts = Site::splitPath($path);
		//$node = $this->server->tree->getNodeForPath($path);
		$rootPath = SiteDavDirectory::filterName($pathParts[0]);
		if(count($pathParts) && array_key_exists($rootPath, SiteDavDirectory::$siteDirectories))
		{
			$className = SiteDavDirectory::$siteDirectories[$rootPath];
			$node = new $className($pathParts[0]);
			
			if(count($pathParts) > 1)
			{
				$node = $node->resolvePath(array_splice($pathParts, 1));
			}
		}
		else
		{
			$node = Site::resolvePath($path, false);
		}

		if(!$node)
			Site::respondNotFound();
                
        
        $children = $node->getChildren();
        
        
        
        if(WebdavRequestHandler::$responseMode == 'json')
        {
            foreach($children as $child)
            {
                $data['children'][] = $child->getData();    
            }
            
            header('Content-type: application/json', true);
            return json_encode($data);
        }
        else
        {
	        $data['children'] = $children; 
	        
	        return TemplateResponse::getSource('Emergence/editor',$data);
        }
    }
}
