<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			$modelPath = $modx->getOption('virtualpage_core_path', null, $modx->getOption('core_path') . 'components/virtualpage/') . 'model/';
			$modx->addPackage('virtualpage', $modelPath);

			$manager = $modx->getManager();
			$objects = array(
				'vpEvent',
				'vpRoute',
				'vpHandler',
			);
			foreach ($objects as $tmp) {
				$manager->createObjectContainer($tmp);
			}

			$level = $modx->getLogLevel();

			$modx->setLogLevel(xPDO::LOG_LEVEL_FATAL);
			$manager->addField('vpHandler', 'content', array('after' => 'entry'));

			$modx->setLogLevel($level);


			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}
return true;
