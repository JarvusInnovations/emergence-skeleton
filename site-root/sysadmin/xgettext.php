<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

// patterns for matching translatable strings
$patternTemplate = '/(_|gettext)\(\s*(\'|")(.*?)\2\s*\)/si';
$patternTemplateShort = '/\{(_|gettext)\s+("|\'|)(.*?)\2\s*\}/si';
$patternPHP = '/(_|gettext)\s*\(\s*(\'|")(.*?)\2\s*\)/si';
$patternPHPValidators = '/(\'|")errorMessage\1\s*=>\s*(\'|")(.*?)\2/s';

// create a memory handle to write pot file to
$pot = fopen('php://memory', 'w+');
$strings = array();
$potFormat = "#: %s\nmsgid \"%s\"\nmsgstr \"\"\n\n";

// extract strings from templates
$files = Emergence_FS::getTreeFiles('html-templates', false, array('Type' => 'text/x-html-template'));

foreach ($files AS $path => $fileData) {
    $contents = file_get_contents(SiteFile::getByID($fileData['ID'])->RealPath);
    _extractStrings($patternTemplate, $contents, $path, $strings);
    _extractStrings($patternTemplateShort, $contents, $path, $strings);
}

// extract strings from PHP files
$files = Emergence_FS::getTreeFiles(null, false, array('Type' => 'application/php'));

foreach ($files AS $path => $fileData) {
    $contents = file_get_contents(SiteFile::getByID($fileData['ID'])->RealPath);
    _extractStrings($patternPHP, $contents, $path, $strings);
    _extractStrings($patternPHPValidators, $contents, $path, $strings);
}

// write pot file
foreach ($strings AS $string => $files) {
    fprintf($pot, $potFormat, implode(' ', $files), addcslashes(stripslashes($string), '"'));
}

rewind($pot);

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="'.Site::getConfig('handle').'.pot"');
fpassthru($pot);


// utility methods
function _extractStrings($pattern, $contents, $fileVirtualPath, &$strings) {
    preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[3] AS list($string, $offset)) {
        $strings[$string][] = $fileVirtualPath . ':' . (substr_count(substr($contents, 0, $offset), "\n") + 1);
    }
}