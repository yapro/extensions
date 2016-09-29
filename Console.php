<?php
function getOptions()
{
    $options = [];
    foreach ($_SERVER['argv'] as $arg) {
        preg_match('/\-\-(\w*)\=?(.+)?/', $arg, $value);
        if (!empty($value[1])) {
            $options[$value[1]] = empty($value[2]) ? '' : $value[2];
        }
    }
    return $options;
}