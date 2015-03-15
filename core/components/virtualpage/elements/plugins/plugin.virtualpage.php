<?php

$virtualpage = $modx->getService('virtualpage', 'virtualpage', $modx->getOption('virtualpage_core_path', null, $modx->getOption('core_path') . 'components/virtualpage/') . 'model/virtualpage/', $scriptProperties);
if(!($virtualpage instanceof virtualpage)) {return '';}
if(!$virtualpage->active) {return '';}
if(isset($_REQUEST['vp_die']) || isset($modx->event->params['vp_die'])) {return '';}
//
$eventName = $modx->event->name;
$listEvent = $virtualpage->getEvents();
//
if(!is_array($listEvent) || (empty($listEvent))) {return '';}
if(in_array($eventName, array_keys($listEvent))) {
	$virtualpage->doRoutes(array_merge($scriptProperties,
		array(
			'eventName' => $eventName,
			'routes' => $listEvent[$eventName],
		)
	));
}