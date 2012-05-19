<?php

interface Dwoo_IDataProvider
{
	/**
	 * returns the data as an associative array that will be used in the template
	 *
	 * @return array
	 */
	public function getData();
}
