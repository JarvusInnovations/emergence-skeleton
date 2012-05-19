<?php



 class HandleBehavior extends RecordBehavior
{


	static public function onSave(ActiveRecord $Record, $handleInput = false)
	{
		// set handle
		if(!$Record->Handle)
		{
			$Record->Handle = $Record::getUniqueHandle($handleInput ? $handleInput : $Record->Title);
		}
	}

	static public function onValidate(ActiveRecord $Record, RecordValidator $validator)
	{
		$validator->validate(array(
			'field' => 'Handle'
			,'required' => false
			,'validator' => 'handle'
			,'errorMessage' => 'Handle can only contain letters, numbers, hyphens, and underscores'
		));
				
		// check handle uniqueness
		if($Record->isFieldDirty('Handle') && !$validator->hasErrors('Handle') && $Record->Handle)
		{
			$ExistingRecord = $Record::getByHandle($Record->Handle);
			
			if($ExistingRecord && ($ExistingRecord->ID != $Record->ID))
			{
				$validator->addError('Handle', 'Handle already registered');
			}
		}
	}
	

}