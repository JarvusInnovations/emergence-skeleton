<?php

/**
 * @deprecated
 * Compatibility layer for Emergence\People\PeopleRequestHandler
 */
class PeopleRequestHandler extends Emergence\People\PeopleRequestHandler
{
    public static function __classLoaded()
    {
        Emergence\Logger::general_warning('Deprecated class loaded: ' . __CLASS__);
    }
}