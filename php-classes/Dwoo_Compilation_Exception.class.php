<?php

class Dwoo_Compilation_Exception extends Dwoo_Exception
{
	protected $compiler;
	protected $template;

	public function __construct(Dwoo_Compiler $compiler, $message)
	{
		$this->compiler = $compiler;
		$this->template = $compiler->getDwoo()->getTemplate();
		parent::__construct('Compilation error at line '.$compiler->getLine().' in "'.$this->template->getResourceName().':'.$this->template->getResourceIdentifier().'" : '.$message);
	}

	public function getCompiler()
	{
		return $this->compiler;
	}

	public function getTemplate()
	{
		return $this->template;
	}
}
