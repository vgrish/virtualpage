<?php

require_once MODX_CORE_PATH . 'model/modx/processors/element/plugin/event/getlist.class.php';
class vpEventGetListProcessor extends modPluginEventGetListProcessor {
	public $classKey = 'modPluginEvent';
	public $languageTopics = array('plugin','system_events');
	public $permission = 'view_plugin';

	public function getData() {
		$exclude = array('mlmscript');
		$exclude = array_merge($exclude, array_map('trim', explode(',', $this->modx->getOption('virtualpage_exclude_events'))));

		$criteria = array();
		$criteria[] = array(
			'groupname:NOT IN' => $exclude,
		);

		$query = $this->getProperty('query');
		if (!empty($query)) {
			$criteria[] = array(
				'name:LIKE' => '%'.$query.'%',
			);
		}

		$this->modx->newQuery('modEvent');
		$eventsResult = $this->modx->call('modEvent', 'listEvents', array(&$this->modx, $this->getProperty('plugin'), $criteria, array(
			$this->getProperty('sort') => $this->getProperty('dir')), $this->getProperty('limit'), $this->getProperty('start')));
		return array(
			'total' => $eventsResult['count'],
			'results' => $eventsResult['collection'],
		);
	}
}
return 'vpEventGetListProcessor';