<?php
class Emergence_Sabre_Dav_Plugin extends Sabre_DAV_ServerPlugin
{
    /**
     * server
     * 
     * @var Sabre_DAV_Server 
     */
    private $server;
    
    public function initialize(Sabre_DAV_Server $server) {

        $this->server = $server;
        //$server->subscribeEvent('unknownMethod',array($this,'unknownMethod'));
        $server->subscribeEvent('beforeMethod',array($this,'beforeMethod'),50);
        //$server->subscribeEvent('afterGetProperties',array($this,'afterGetProperties'));

        $server->subscribeEvent('beforeCreateFile', array($this,'beforeCreateFile'));
    }
    /**
     * This method is called before the logic for any HTTP method is
     * handled.
     *
     * This plugin uses that feature to intercept access to locked resources.
     * 
     * @param string $method
     * @param string $uri
     * @return bool 
     */
    public function beforeMethod($method, $uri) {

        switch($method) {

            case 'GET' :
                if (!$node) $node = $this->server->tree->getNodeForPath($uri);
                $this->server->httpResponse->setHeader('X-VFS-ID',$node->ID);
                break;
            case 'PUT' :
                if(stripos($uri, '_parent/') === 0)
                {
                    $this->server->httpResponse->setHeader('Location','http://'.$_SERVER['HTTP_HOST'].'/develop/'.str_replace('_parent/',null,$uri));        
                }
                break;
        }

        return true;

    }
    
    
    public function beforeCreateFile($uri, $data)
    {
    	list($dir, $name) = Sabre_DAV_URLUtil::splitPath($uri);
    	
		$currentNode = null;
		foreach(explode('/',trim($dir,'/')) as $pathPart)
		{
			$parentNode = $currentNode;
			$currentNode = SiteCollection::getByHandle($pathPart, $parentNode?$parentNode->ID:null);

			if(!$currentNode)
				$currentNode = SiteCollection::create($pathPart, $parentNode);
		}
    }
}