<?php

namespace Emergence\Connectors;

class SyncResult
{
    const STATUS_CREATED = 'created';
    const STATUS_UPDATED = 'updated';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_VERIFIED = 'verified';
    const STATUS_DELETED = 'deleted';

    protected $status;
    protected $message = '';
    protected $context = [];

    public function __construct($status, $message, array $context = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->context = $context;
    }

    public function __toString()
    {
        return $this->getStatus();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getMessage()
    {
#        return $this->message;
        return static::interpolate($this->message, $this->context);
    }

    public function getContext($key = null)
    {
        if (isset($key)) {
            return $this->context[$key];
        } else {
            return $this->context;
        }
    }

    public static function interpolate($message, array $context = array())
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

}