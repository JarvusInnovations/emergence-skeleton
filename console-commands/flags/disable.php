<?php

if (empty($_COMMAND['ARGS'])) {
    die('Usage: flags:disable <key>');
}

Cache::delete("flags/{$_COMMAND['ARGS']}");

$_COMMAND['LOGGER']->info("Deleted flags/{key}", compact('key'));
