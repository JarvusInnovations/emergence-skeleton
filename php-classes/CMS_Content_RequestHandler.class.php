<?php
class CMS_Content_RequestHandler extends CRUDRequestHandler
{
	static public $recordClass = 'CMS_Content';
    static public $accountLevelRead = false;
    static public $accountLevelBrowse = false;
    static public $accountLevelWrite = 'Staff';
    static public $accountLevelAPI = 'Staff';
    static public $browseOrder = array('Published' => 'DESC');
    
	static public function handleRequest()
	{
		if(empty(static::$recordClass))
			throw new Exception('static public $recordClass must be set to an ActiveRecord implementation');
			
			
		if(static::peekPath() == 'json')
			static::$responseMode = static::shiftPath();
			
		
		switch($action ? $action : $action = static::peekPath())
		{
			case 'post':
			{	
				static::shiftPath();
				return static::handlePostRequest();
			}
			case 'edit':
			{
				static::shiftPath();
				if($Content = CMS_BlogPost::getByID(static::peekPath()))
				{
					return static::handleEditRequest($Content);
				}
				else {
					return static::throwNotFoundError();
				}
			}
			default:
			{				
				return parent::handleRequest();
			}
		}
	}
	
	static public function handleEditRequest(CMS_Content $Content) {
		$data = static::getRequestData();
		
		//Debug::dump($Content);
		//Debug::dump($Content->Items);
		
		// edit Content Object
		$Content->Title = $data['Title'];
		$Content->save();
		
		// edit text ContentItem object
		$ContentItem = array_shift($Content->Items); // we need the first element out of the array but the key will not always be 0. The key is the actually the object's SQL assigned ID
		$ContentItem->Data = $data['Data'];
		$ContentItem->save();
		
		// edit Categories
		$CategoryItems = CategoryItem::getAllByWhere("`ContextClass`='CMS_BlogPost' AND `ContextID`='{$Content->ID}'");
		if(count($CategoryItems))
		{
			// check which need to be removed
			foreach($CategoryItems as $CategoryItem)
			{
				if(!in_array($CategoryItem->CategoryID,$data['Categories'])) // if existing CategoryItem isn't found in the input, destroy it
				{
					$CategoryItem->destroy();
				}
			}
			
			// go through input data and create new CategoryItems if not found
			if(is_array($data['Categories']) && count($data['Categories']))
			{
				foreach($data['Categories'] as $cat) {
					$found = false;
					foreach($CategoryItems as $CategoryItem)
					{
						if($CategoryItem->CategoryID == $cat)
						{
							$found = true;
						}
					}
					
					if(!$found)
					{
						$Category = Category::getByID($cat);
						$CategoryItem = new CategoryItem();
						$CategoryItem->ContextClass = 'CMS_BlogPost';
						$CategoryItem->ContextID = $Content->ID;
						$CategoryItem->CategoryID = $Category->ID;
						$CategoryItem->save();	
					}
				}	
			}
		}
		//Debug::dump($data['Categories']);
		
		
		// edit Tags
		$TagItems = TagItem::getAllByWhere("`ContextClass`='CMS_BlogPost' AND `ContextID`='{$Content->ID}'");
		if(count($TagItems))
		{
			// check which need to be removed
			foreach($TagItems as $TagItem)
			{
				$Tag = Tag::getByID($TagItem->TagID);
				if(!in_array($Tag->Title,$data['Tags'])) // if existing Tag isn't found in the input, destroy it's TagItem
				{
					$TagItem->destroy();
				}
			}
			
			// go through input data and create new TagItems if not found
			if(is_array($data['Tags']) && count($data['Tags']))
			{
				foreach($data['Tags'] as $word) {
					$found = false;
					foreach($TagItems as $TagItem)
					{
						$Tag = Tag::getByID($TagItem->TagID);
						if($Tag->Title == $word)
						{
							$found = true;
						}
					}
					
					if(!$found)
					{
						$Tag = Tag::getFromHandle($word,true); // second boolean is telling method to make the Tag object for us if it doesn't exist
						$TagItem = new TagItem();
						$TagItem->ContextClass = 'CMS_BlogPost';
						$TagItem->ContextID = $Content->ID;
						$TagItem->TagID = $Tag->ID;
						$TagItem->save();
					}
				}	
			}
		}
		
		
		//Debug::dump($data['Tags']);
		//Debug::dump($Content->Tags);
		
		return static::respondCRUD($Content, 'singular', 'updated');
	}
	
	// post as in make a blog post
	static public function handlePostRequest() {
		$data = static::getRequestData();
		
		// create Content object
		$Content = new CMS_BlogPost();
		$Content->Title = $data['Title'];
		$Content->save();
		
		// create text ContentItem object
		$ContentItem = new CMS_RichTextContent();
		$ContentItem->ContentID = $Content->ID;
		$ContentItem->Data = $data['Data'];
		$ContentItem->save();
		
		// create CategoryItem object foreach
		if(is_array($data['Categories']) && count($data['Categories']))
		{
			foreach($data['Categories'] as $cat) {
				$Category = Category::getByID($cat);
				$CategoryItem = new CategoryItem();
				$CategoryItem->ContextClass = 'CMS_BlogPost';
				$CategoryItem->ContextID = $Content->ID;
				$CategoryItem->CategoryID = $Category->ID;
				$CategoryItem->save();
			}
		}
		
		// create TagItem object foreach. if Tag object doesn't exist, create as well
		if(is_array($data['Tags']) && count($data['Tags']))
		{
			foreach($data['Tags'] as $word) {
				$Tag = Tag::getFromHandle($word,true); // second boolean is telling method to make the Tag object for us if it doesn't exist
				$TagItem = new TagItem();
				$TagItem->ContextClass = 'CMS_BlogPost';
				$TagItem->ContextID = $Content->ID;
				$TagItem->TagID = $Tag->ID;
				$TagItem->save();
			}
		}
		
		return static::respondCRUD($Content, 'singular', 'created');
	}
}