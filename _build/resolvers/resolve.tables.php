<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('virtualpage_core_path', null, $modx->getOption('core_path') . 'components/virtualpage/') . 'model/';
			$modx->addPackage('virtualpage', $modelPath);

			$manager = $modx->getManager();
			$objects = array(
				'vpEvent',
				'vpRoute',
			);
			foreach ($objects as $tmp) {
				$manager->createObjectContainer($tmp);
			}
			break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;
