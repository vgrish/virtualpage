<?php

/** @var array $scriptProperties */
$fqn = $modx->getOption('virtualpage_class', null, 'virtualpage.virtualpage', true);
$path = $modx->getOption('virtualpage_class_path', null,
    $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/virtualpage/');
/** @var virtualpage $virtualpage */
if (!$virtualpage = $modx->getService($fqn, '', $path . 'model/', array('core_path' => $path))) {
    return false;
}

$className = 'virtualpage' . $modx->event->name;
$modx->loadClass('virtualpagePlugin', $path . 'model/virtualpage/systems/', true, true);
$modx->loadClass($className, $path . 'model/virtualpage/systems/', true, true);
if (class_exists($className)) {
    /** @var $virtualpage $plugin */
    $plugin = new $className($modx, $scriptProperties);
    $plugin->run();
}
return;