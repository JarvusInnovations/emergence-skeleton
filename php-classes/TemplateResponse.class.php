<?php



class TemplateResponse extends Dwoo
{

	// configurables
	static public $MagicGlobals = array('Session');
	static public $pathCompile = '/tmp/dwoo-compiled';
	static public $pathCache = '/tmp/dwoo-cached';
	
    static public $onGlobalsSet;
    static public $templateResources = array(
		'Emergence' => 'TemplateResource'
    );
    static public $defaultTemplateResource = 'Emergence';


	// protected properties
	static protected $_instance_dwoo;
	static protected $_instance_compiler;
	static protected $_instance_dwoo_loader;

	// contructor
	function __construct()
	{
		if(!file_exists(static::$pathCompile)) {
			mkdir(static::$pathCompile);
		}
		if(!file_exists(static::$pathCache)) {
			mkdir(static::$pathCache);
		}
		
		// call parent
		parent::__construct(static::$pathCompile, static::$pathCache);
		
		// configure instance
		//$this->getLoader()->addDirectory(static::$pathPlugins);
		
        // register template resources
        foreach(static::$templateResources AS $handle => $class)
    	    $this->addResource($handle, $class, array(__CLASS__, 'compilerFactory'));
	}
	
	public function getLoader() {
		if(!isset(static::$_instance_dwoo_loader))
		{
			static::$_instance_dwoo_loader = new Emergence_Dwoo_Loader();
		}
		return static::$_instance_dwoo_loader;
	}
	
	// static methods
	public static function getInstance()
	{
		if (!isset(static::$_instance_dwoo))
		{
			static::$_instance_dwoo = new self();
		}
		
		return static::$_instance_dwoo;
	}
	
	
	public static function compilerFactory()
	{
		if (!isset(static::$_instance_compiler))
		{
			static::$_instance_compiler = Dwoo_Compiler::compilerFactory();
		}
		
		return static::$_instance_compiler;
	}
	
	
	public static function respond($template, $data = array(), $factory = null)
	{
		if(is_string($template))
		{
			$template = static::getInstance()->templateFactory($factory ? $factory : static::$defaultTemplateResource, $template.'.tpl');
		}
		elseif(is_a($template, 'SiteFile'))
		{
			$template = new TemplateResource($template);
		}
		static::getInstance()->output($template, $data);
		exit;
	}
	
	

	public static function getSource($template, $data = array(), $factory = null)
	{
    	if(is_string($template))
		{
			$template = static::getInstance()->templateFactory($factory ? $factory : static::$defaultTemplateResource, $template.'.tpl');
		}
		elseif(is_a($template, 'SiteFile'))
		{
			$template = new TemplateResource($template);
		}
        
		return self::getInstance()->get($template, $data);
	}

	
	
	// overrides
	protected function initRuntimeVars(Dwoo_ITemplate $tpl)
	{
		// call parent
		parent::initRuntimeVars($tpl);
		
		// set site information
		$this->globals['Site'] = array(
			'Name' => Site::$Title
		);
		
		// add magic globals
		foreach(self::$MagicGlobals AS $name)
		{
			if (isset($GLOBALS[$name]))
			{
				$this->globals[$name] = &$GLOBALS[$name];
			}
			else
			{
				$this->globals[$name] = false;
			}
		}
		
		// set user
		$this->globals['User'] = $GLOBALS['Session']->Person ? $GLOBALS['Session']->Person : null;

	}

	
	/*public function output($resourceId, $data = array(), $compiler = null)
	{
		$template = self::getInstance()->templateFactory('MICS', $resourceId);
		
		if($template)
		{
			return parent::output($template, $data, $compiler);
		}
		else
		{
			throw new Exception('Failed to instantiate template');
		}
	}*/
	


}