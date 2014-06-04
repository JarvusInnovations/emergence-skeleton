<?php

class ContentsRequestHandler extends RecordsRequestHandler
{
	static public $recordClass = 'Content';
	
	static public $accountLevelRead = 'Staff';
	static public $accountLevelComment = 'Staff';
	static public $accountLevelBrowse = 'Staff';
	static public $accountLevelWrite = 'Staff';
	static public $accountLevelAPI = 'Staff';
}