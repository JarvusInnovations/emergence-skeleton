<?php

class TemplatePrinter
{
    public static $wkhtmltopdfPath = '/usr/local/bin/wkhtmltopdf';
    public static $tmpDir = '/tmp';
    public static $tmpPrefix = 'wkhtmltopdf-';


    public static function html2pdf($html)
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

    public static function respondPDF($pdf, $filename = 'printout')
    {
        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename=\"$filename.pdf\"");
        header('Content-Length: '.filesize($pdf));
        readfile($pdf);
        exit();
    }

    public static function template2pdf($template, $data)
    {
        return static::html2pdf(TemplateResponse::getSource($template, $data));
    }

    public static function template2response($template, $data, $filename = 'printout')
    {
        return static::respondPDF(static::template2pdf($template, $data), $filename);
    }

    public static function html2response($html, $filename = 'printout')
    {
        return static::respondPDF(static::html2pdf($html), $filename);
    }
}