<?php

function Dwoo_Plugin_html_attributes_encode(Dwoo_Core $dwoo, $array, $prefix = 'data-', $exclude = [], $deep = true)
{
    $attributes = [];

    if (is_string($exclude)) {
        $exclude = explode(',', $exclude);
    }

    foreach ($array as $key => $value) {
        if ($value === false || $value === null || in_array($key, $exclude)) {
            continue;
        }

        $attribute = ($prefix ?: '').$key;

        if ($value !== true) {
            if (!is_scalar($value)) {
                if ($deep) {
                    $value = json_encode($value);
                } else {
                    continue;
                }
            }

            $attribute .= '="'.htmlspecialchars($value).'"';
        }

        $attributes[] = $attribute;
    }

    return implode(' ', $attributes);
}
