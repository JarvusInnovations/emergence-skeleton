<?php

class Content extends VersionedRecord
{
	// VersionedRecord configuration
	static public $historyTable = 'history_content';

	// ActiveRecord configuration
	static public $tableName = 'content';
	static public $singularNoun = 'content';
	static public $pluralNoun = 'contents';
	
	static public $fields = array(
		'Handle' => array(
			'unique' => true
		)
		,'Renderer' => array(
			'type' => 'enum'
			,'values' => array('text', 'html', 'markdown')
			,'default' => 'markdown'
		)
		,'Content' => array(
			'type' => 'clob'
		)
	);
	
	
	static public function getByHandle($contentHandle)
	{
		return static::getByField('Handle', $contentHandle, true);
	}

	public function validate($deep = true)
	{
		// call parent
		parent::validate($deep);
		
		$this->_validator->validate(array(
			'field' => 'Handle'
			,'validator' => 'handle'
			,'errorMessage' => 'Handle can only contain letters, numbers, hyphens, and underscores'
		));
		
		// save results
		return $this->finishValidation();
	}
	
	public function getHtml()
	{
		switch ($this->Renderer) {
			case 'text':
				return htmlspecialchars($this->Content);
			case 'html':
				return $this->Content;
			case 'markdown':
				return 
                    \Michelf\SmartyPantsTypographer::defaultTransform(
                        \Michelf\MarkdownExtra::defaultTransform($this->Content)
                    );
		}
		
		return '';
	}
}