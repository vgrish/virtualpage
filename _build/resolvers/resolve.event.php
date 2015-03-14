<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:

			// Plugin Events
			$events = array(
				'1' => array(
					'name' => 'OnPageNotFound',

				),
				'2' => array(
					'name' => 'OnHandleRequest',
					'active' => 0,
				)
			);

			foreach ($events as $id => $properties) {
				if (!$event = $modx->getCount('vpEvent', array('id' => $id))) {
					$event = $modx->newObject('vpEvent', array_merge(array(
						'active' => 1,
						'rank' => $id - 1,
					), $properties));
					$event->set('id', $id);
					$event->save();
				}
			}

			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;