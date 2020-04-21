<?php

// test disconnection from skeleton.mics.me
if (Site::$hostname == 'skeleton.emr.ge') {
    Site::$autoPull = false;
}

Site::$debug = true; // set to true for extended query logging
//Site::$production = true; // set to true for heavy file caching


// these resolved paths will skip initializing a user session
Site::$skipSessionPaths[] = 'thumbnail/';
Site::$skipSessionPaths[] = 'min/';

// uncomment or set to an array of specific hostnames to enable CORS
//Site::$permittedOrigins = '*';

// Custom routing called if a page isn't found in site-root
/*Site::$onNotFound = function($message) {
    switch($action = Site::$requestPath[0])
    {
        default:
            if($Page = Page::getByHandle($action))
            {
                return Page::renderPage();
            }
            else
            {
                header('HTTP/1.0 404 Not Found');
                die($message);
            }
    }
};*/