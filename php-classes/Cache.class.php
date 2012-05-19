<?php

class Cache
{

	static public function localizeKey($key)
	{
		return $_SERVER['HTTP_HOST'].':'.$key;
	}

	static public function fetch($key)
	{
		return apc_fetch(static::localizeKey($key));
	}
	
	static public  function store($key, $value, $ttl)
	{
		return apc_store(static::localizeKey($key), $value, $ttl);
	}
	
	static public function delete($key)
	{
		return apc_delete(static::localizeKey($key));
	}

}