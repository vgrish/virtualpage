<?php

/** @var $modx modX */
if (!$modx = $object->xpdo AND !$object->xpdo instanceof modX) {
    return true;
}

if (!version_compare(PHP_VERSION, '5.4', '>=')) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Invalid php version. Minimal supported version – 5.4');

    die;
}

return true;