<?php

class TemplatePrinter
{
	static public $wkhtmltopdfPath = '/usr/local/bin/wkhtmltopdf';
	static public $tmpDir = '/tmp';
	static public $tmpPrefix = 'wkhtmltopdf-';
	
	
	static public function html2pdf($html)
	{
		// get a temp filename prefix
		$filePath = tempnam(static::$tmpDir, static::$tmpPrefix);
		
		// write html to file
		file_put_contents($filePath.'.html', $html);
		
		// execute wkhtmltopdf
		$command = sprintf('%s "%s.html" "%s.pdf"', static::$wkhtmltopdfPath, $filePath, $filePath);
		exec($command);

		return $filePath.'.pdf';
	}
	
	static public function respondPDF($pdf, $filename = 'printout')
	{
		header('Content-Type: application/pdf');
		header("Content-Disposition: attachment; filename=\"$filename.pdf\"");
		header('Content-Length: '.filesize($pdf));
		readfile($pdf);
		exit();
	}

	static public function template2pdf($template, $data)
	{
		return static::html2pdf(TemplateResponse::getSource($template, $data));
	}
	
	static public function template2response($template, $data, $filename = 'printout')
	{
		return static::respondPDF(static::template2pdf($template, $data), $filename);
	}
	
	static public function html2response($html, $filename = 'printout')
	{
		return static::respondPDF(static::html2pdf($html), $filename);
	}
	
}