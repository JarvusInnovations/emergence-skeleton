<?php

interface Dwoo_ICompiler
{
	/**
	 * compiles the provided string down to php code
	 *
	 * @param string $templateStr the template to compile
	 * @return string a compiled php code string
	 */
	public function compile(Dwoo_Core $dwoo, Dwoo_ITemplate $template);

	/**
	 * adds the custom plugins loaded into Dwoo to the compiler so it can load them
	 *
	 * @see Dwoo_Core::addPlugin
	 * @param array $customPlugins an array of custom plugins
	 */
	public function setCustomPlugins(array $customPlugins);

	/**
	 * sets the security policy object to enforce some php security settings
	 *
	 * use this if untrusted persons can modify templates,
	 * set it on the Dwoo object as it will be passed onto the compiler automatically
	 *
	 * @param Dwoo_Security_Policy $policy the security policy object
	 */
	public function setSecurityPolicy(Dwoo_Security_Policy $policy = null);
}
