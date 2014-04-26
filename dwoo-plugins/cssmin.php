<?php

function Dwoo_Plugin_cssmin(Dwoo_Core $dwoo, $files, $root = array('site-root','css'))
{
	if (is_array($files)) {
		$files = implode('+', $files);
	}
	
	// analyze tree to obtain hash and file map
	$sourceReport = MinifiedRequestHandler::getSourceReport($files, $root, 'text/css');
	
	if(!empty($_GET['css-debug']) || !empty($_GET['cssdebug'])) {
		$html = '';
		foreach($sourceReport['files'] AS $filename => $fileData) {
			$html .= "<link rel='stylesheet' type='text/css' href='".preg_replace('/^site-root/', '', $filename)."?_sha1=$fileData[SHA1]'>\n";
		}
		
		return $html;
	}
	
	return "<link rel='stylesheet' type='text/css' href='/min/css/$files?_sha1=$sourceReport[hash]'>";
}

