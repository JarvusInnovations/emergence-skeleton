<?php

// test disconnection from skeleton.mics.me
if (Site::$hostname == 'skeleton.emr.ge') {
    Site::$autoPull = false;
}

Site::$debug = true; // set to true for extended query logging
//Site::$production = true; // set to true for heavy file caching


// these resolved paths will skip initializing a user session
Site::$skipSessionPaths[] = 'thumbnail.php';

// uncomment or set to an array of specific hostnames to enable CORS
//Site::$permittedOrigins = '*';