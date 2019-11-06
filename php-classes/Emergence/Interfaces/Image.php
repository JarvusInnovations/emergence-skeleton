<?php

namespace Emergence\Interfaces;

interface Image
{
    public function getImage(array $options = []);
    public function getImageUrl($width, $height = null, array $options = []);
}
