<?php

function Dwoo_Plugin_versioned_url(Dwoo_Core $dwoo, $path, $source = 'site-root', $includeHost = false)
{
    $trimmedPath = ltrim($path, '/');

    if ($source == 'site-root') {
        $url = Site::getVersionedRootUrl($trimmedPath);

        if ($includeHost) {
            $url = (Site::getConfig('ssl') ? 'https' : 'http').'://'.Site::getConfig('primary_hostname').$url;
        }

        return $url;
    } else {
        return $path;
    }
}