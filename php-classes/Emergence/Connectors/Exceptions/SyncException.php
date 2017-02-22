<?php

namespace Emergence\Connectors\Exceptions;

class SyncException extends \Exception
{
    private $message;
    private $context;

    public function __construct($message, array $context = [])
    {
        $this->message = $message;
        $this->context = $context;

        return parent::__construct($message);
    }

    public function getMessage($interpolate = true)
    {
        return $interpolate ? static::interpolate($this->message, $this->context) : $this->message;
    }

    public function getContext($key = null)
    {
        if ($key && is_array($this->context)) {
            return $this->context[$key];
        }

        return $this->context;
    }

    protected static function interpolate($message, array $context = array())
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