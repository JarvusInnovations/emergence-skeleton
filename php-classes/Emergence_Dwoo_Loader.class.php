<?php
class Emergence_Dwoo_Loader implements Dwoo_ILoader
{
    public function loadPlugin($class, $forceRehash = true)
    {

        $VFS_Paths = array(
            'builtin/blocks/'
            ,'builtin/filters/'
            ,'builtin/functions/'
            ,'builtin/processors/'
            ,'builtin/'
            ,'personal/'
            ,'thirdparty/'
        );

		if($class == 'array') {
			$class = 'helper.array';
		}

        $localRoot = Site::getRootCollection('dwoo-plugins');

        foreach($VFS_Paths as $virtualPath)
        {
            $templatePath = Site::splitPath($virtualPath.$class.'.php');

            if($pluginNode = $localRoot->resolvePath($templatePath))
            {
                break;
            }

            if($pluginNode = Emergence::resolveFileFromParent('dwoo-plugins', $templatePath))
            {
                break;
            }

        }

        if(file_exists($pluginNode->RealPath))
        {
            require($pluginNode->RealPath);
        }
        else {
            throw new Dwoo_Exception('Plugin <em>'.$class.'</em> can not be found in the Emergence VFS.');
        }

    }

}