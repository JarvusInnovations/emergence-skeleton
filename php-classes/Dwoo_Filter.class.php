<?php

abstract class Dwoo_Filter
{
	/**
	 * the dwoo instance that runs this filter
	 *
	 * @var Dwoo
	 */
	protected $dwoo;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 *
	 * @param Dwoo_Core $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Dwoo_Core $dwoo)
	{
		$this->dwoo = $dwoo;
	}

	/**
	 * processes the input and returns it filtered
	 *
	 * @param string $input the template to process
	 * @return string
	 */
	abstract public function process($input);
}
