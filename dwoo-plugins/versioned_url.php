<?php

function Dwoo_Plugin_versioned_url(Dwoo_Core $dwoo, $path, $source = 'site-root')
{
    $trimmedPath = ltrim($path, '/');
    
    if ($source == 'site-root') {
        return Site::getVersionedRootUrl($trimmedPath);
    } else {
        return $path;
    }
}

