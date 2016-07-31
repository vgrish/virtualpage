<?php

/** @var $modx modX */
if (!$modx = $object->xpdo AND !$object->xpdo instanceof modX) {
    return true;
}

/** @var $options */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:

        // Plugin Events
        $events = array(
            array(
                'name' => 'OnPageNotFound',
            ),
            array(
                'name' => 'OnHandleRequest',
            )
        );

        foreach ($events as $row) {
            if (!$event = $modx->getCount('vpEvent', array('name' => $row['name']))) {
                $event = $modx->newObject('vpEvent', array_merge(array(
                    'rank' => $modx->getCount('vpEvent'),
                ), $row));
                $event->save();
            }
        }

        break;

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return true;