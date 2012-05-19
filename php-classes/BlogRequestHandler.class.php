<?php
class BlogRequestHandler extends RequestHandler {

    /*
    
    Sorry for wall of text :P
    
    Models:
        Pages extends Content
        Blog extends Content
        
        ^-- Shares table :)
    

    
    /// Default request handles assigned to blog posts:
    
    ** Ability to change in settings should exist. **
    
    [Default]   /{%year%}/{%month%}/{%posthandle%}
                /{%year%}/{%month%}/{%day%}/{%posthandle%}
                /archive/{%posthandle%/%post_id%}
                [Custom] (Input field)
                
    Custom field specified from:
    
        {%year%} {%month%} {%day%} {%hour%} {%minute%} {%second%}
    
        {%author%} {%post_id%} {%posthandle%} {%category%}
    
    /// Supportable request handles:
    
    Ok, so. We need to do all the following:
    
    ** Order of appearence dictates priority **
    
    All of these should work regardless of the default:

    Pages:
    /{%pagehandle%}

    Time:
    /{%year%}
    /{%year%}/{%posthandle%/%post_id%}
    /{%year%}/{%month%}
    /{%year%}/{%month%}/{%posthandle%/%post_id}
    /{%year%}/{%month%}/{%day%}
    /{%year%}/{%month%}/{%day%}/{%posthandle%/%post_id%}

    Categories:
    /{%category%}[/{%category}]
    /{%category%}[/{%category}]/{%posthandle%/%post_id%}
    
    Tags:
    /tag/{%tag%}/{%posthandle%/%post_id%}
    
    Archive Special:
      ** This is designed for post IDs so you can get to any post from here in archive fashion, but fuck it let it try to resolve the posthandle too **
    /archive/{%posthandle%/%post_id%}
    
     */

    static public function handleRequest() {
    	
        // this isn't handled in the Site class for some reason
        Site::$pathStack = Site::$requestPath = Site::splitPath(Site::$requestURI['path']);
        static::setPath();
        $GLOBALS['Session'] = UserSession::getFromRequest();
        /* CRAPPY HACK END */
        
        
        if(static::peekPath()) {
        	switch($action ? $action : $action = static::shiftPath())
        	{
        		
        		case 'blog-admin':
        		{
        			return BlogAdminRequestHandler::handleRequest();
        		}
        		
        	}
        	
        	// check if category
        	if($Category = Category::getByHandle($action)) {
				static::respond('blog/home', array(
	            	'Session' => $GLOBALS['Session']
	            	,'Category' => $Category
	            	,'Categories' => Category::getAllByWhere("`ParentID`='0'")
	            	,'Content' => $Category->Items
	            ));
        	}
        }
        else {
            static::respond('blog/home', array(
            	'Session' => $GLOBALS['Session']
            	,'Categories' => Category::getAllByWhere("`ParentID`='0'")
            	,'Content' => CMS_Content::getAll(array('order' => array('Created'=>'DESC')))
            ));
        }
        
    }
    
}