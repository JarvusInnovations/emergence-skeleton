<?php

abstract class Dwoo_Processor
{
	/**
	 * the compiler instance that runs this processor
	 *
	 * @var Dwoo
	 */
	protected $compiler;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 *
	 * @param Dwoo_Core $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Dwoo_Compiler $compiler)
	{
		$this->compiler = $compiler;
	}

	/**
	 * processes the input and returns it filtered
	 *
	 * @param string $input the template to process
	 * @return string
	 */
	abstract public function process($input);
}
