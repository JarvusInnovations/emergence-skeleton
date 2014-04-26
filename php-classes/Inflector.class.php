<?php

class Inflector
{
    static public function spacifyCaps($string)
	{
		return preg_replace('/([a-z])([A-Z])/', '$1 $2', $string);
	}
	
	static public function pluralize($noun, $count = 2)
	{
		if ($count == 1) {
			return $noun;
		} else {
			return $noun . 's';
		}
	}
	
	static public function pluralizeRecord($Record, $count = 2)
	{
		if ($count == 1) {
			return $Record::$singularNoun;
		} else {
			return $Record::$pluralNoun;
		}
	}

    static public function labelIdentifier($string)
    {
        $string = ucfirst(strtolower(static::spacifyCaps($string)));
        
        return preg_replace('/\bid(s?)\b/', 'ID$1', $string);
    }
}