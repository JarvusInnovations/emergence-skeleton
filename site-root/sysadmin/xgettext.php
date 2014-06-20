<?php

$GLOBALS['Session']->requireAccountLevel('Developer');

// patterns for matching translatable strings
$patternTemplate = '/(_|gettext)\(\s*(\'|")(.*?)\2\s*\)/';
$patternTemplateShort = '/\{(_|gettext)\s+("|\'|)(.*?)\2\s*\}/';
$patternPHP = '/(_|gettext)\s*\(\s*(\'|")(.*?)\2\s*\)/';
$patternPHPValidators = '/(\'|")errorMessage\1\s*=>\s*(\'|")(.*?)\2/';

// create a memory handle to write pot file to
$pot = fopen('php://memory', 'w+');
$strings = array();
$potFormat = "#: %s\nmsgid \"%s\"\nmsgstr \"\"\n\n";

// extract strings from templates
$files = Emergence_FS::getTreeFiles('html-templates', false, array('Type' => 'text/x-html-template'));

foreach ($files AS $path => $fileData) {
    $node = SiteFile::getByID($fileData['ID']);
    _extractStrings($patternTemplate, $node->RealPath, $path, $strings);
    _extractStrings($patternTemplateShort, $node->RealPath, $path, $strings);
}

// extract strings from PHP files
$files = Emergence_FS::getTreeFiles(null, false, array('Type' => 'application/php'));

foreach ($files AS $path => $fileData) {
    $node = SiteFile::getByID($fileData['ID']);
    _extractStrings($patternPHP, $node->RealPath, $path, $strings);
    _extractStrings($patternPHPValidators, $node->RealPath, $path, $strings);
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
function _extractStrings($pattern, $fileRealPath, $fileVirtualPath, &$strings) {
    $lines = file($fileRealPath);

    foreach (preg_grep($pattern, $lines) AS $lineNo => $line) {
        preg_match_all($pattern, $line, $matches);

        foreach ($matches[3] AS $string) {
            $strings[$string][] = $fileVirtualPath . ':' . ($lineNo + 1);
        }
    }
}