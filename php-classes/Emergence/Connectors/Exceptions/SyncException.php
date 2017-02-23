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

    public static function interpolate($message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = (string)$value;
        }

        return strtr($message, $replace);
    }
}