<?php

/** @var $modx modX */
if (!$modx = $object->xpdo AND !$object->xpdo instanceof modX) {
    return true;
}

/** @var $options */
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
        break;
    case xPDOTransport::ACTION_UPGRADE:

        $fqn = $modx->getOption('virtualpage_class', null, 'virtualpage.virtualpage', true);
        $path = $modx->getOption('virtualpage_class_path', null,
            $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/virtualpage/');
        /** @var virtualpage $virtualpage */
        if (!$virtualpage = $modx->getService($fqn, '', $path . 'model/', array('core_path' => $path))) {
            die;
        }

        if (!property_exists($virtualpage, 'version') || version_compare($virtualpage->version, '2.0.0-beta', '<')) {
            $modx->log(modX::LOG_LEVEL_ERROR, '[virtualpage] You need to remove first old version "virtualpage"');

            die;
        }

        break;

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}

return true;