<?php

namespace Emergence;

class KeyedDiff
{
    private $newValues;
    private $oldValues;

    public function __construct(array $newValues, array $oldValues = null)
    {
        $this->newValues = $newValues;
        $this->oldValues = $oldValues;
    }

    public function getNewValues()
    {
        return $this->newValues;
    }

    public function getOldValues()
    {
        return $this->oldValues;
    }

    public function getValues()
    {
        return [
            'new' => $this->newValues,
            'old' => $this->oldValues
        ];
    }
}
