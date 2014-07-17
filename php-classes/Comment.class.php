<?php

/**
 * @deprecated
 * Compatibility layer for Emergence\Comments\Comment
 */
class Comment extends Emergence\Comments\Comment
{
    static function __classLoaded()
    {
        Emergence\Logger::general_warning('Deprecated class loaded: ' . __CLASS__);
    }
}