<?php

/** @var $modx modX */
if (!$modx = $object->xpdo AND !$object->xpdo instanceof modX) {
    return true;
}

/** @var $options */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:

        $modx->removeCollection('modPlugin', array(
            'name:IN' => array(
                'vpService',
                'vpEvent'
            )
        ));

        break;

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return true;