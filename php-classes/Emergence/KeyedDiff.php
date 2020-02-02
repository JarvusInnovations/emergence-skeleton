<?php

namespace Emergence;

class KeyedDiff
{
    private $newValues;
    private $oldValues;

    public function __construct(array $newValues = [], array $oldValues = [])
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

    public function addChange($key, $newValue, $oldValue = null)
    {
        $this->newValues[$key] = $newValue;
        $this->oldValues[$key] = $oldValue;
    }

    public function getDelta()
    {
        $delta = [];

        foreach ($this->newValues as $key => $newValue) {
            $oldValue = isset($this->oldValues[$key]) ? $this->oldValues[$key] : null;

            if ($newValue === $oldValue) {
                continue;
            }

            $delta[$key] = [ 'old' => $oldValue, 'new' => $newValue ];
        }

        return $delta;
    }

    public function hasChanges()
    {
        return count($this->getDelta()) > 0;
    }
}
