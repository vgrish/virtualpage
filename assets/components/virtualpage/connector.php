<?php
$productionConfig = dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
$developmentConfig = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
if (file_exists($productionConfig)) {
	/** @noinspection PhpIncludeInspection */
	require_once $productionConfig;
} else {
	/** @noinspection PhpIncludeInspection */
	require_once $developmentConfig;
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var virtualpage $virtualpage */
$virtualpage = $modx->getService('virtualpage', 'virtualpage', $modx->getOption('virtualpage_core_path', null, $modx->getOption('core_path') . 'components/virtualpage/') . 'model/virtualpage/');
$modx->lexicon->load('virtualpage:default');

// handle request
$corePath = $modx->getOption('virtualpage_core_path', null, $modx->getOption('core_path') . 'components/virtualpage/');
$path = $modx->getOption('processorsPath', $virtualpage->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));