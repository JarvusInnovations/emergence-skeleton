<?php



 class GlobalHandleBehavior extends HandleBehavior
{


	static public function onSave(ActiveRecord $Record, $handleInput = false)
	{
		// set handle
		if(!$Record->Handle)
		{
			$Record->GlobalHandle = GlobalHandle::createAlias($Record, $handleInput);
		}
	}
	

}