<?php

class vpPluginEventGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'modEvent';
	public $languageTopics = array('plugin','system_events');
	public $permission = 'view_plugin';

	/** {@inheritDoc} */
	public function initialize() {
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}
		return parent::initialize();
	}

	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$exclude = array('virtualpage');
		$exclude = array_merge($exclude, array_map('trim', explode(',', $this->modx->getOption('virtualpage_exclude_event_groupname'))));

		$eventColumns = $this->modx->getSelectColumns('modEvent', 'modEvent', '', array(), true);
		$c->select($eventColumns);
		$c->where(array('groupname:NOT IN' => $exclude));
		if ($query = $this->getProperty('query')) {
			$c->where(array(
				'name:LIKE' => '%'.$query.'%',
			));
		}

		return $c;
	}
	/** {@inheritDoc} */
	public function getData() {
		$data = array();
		$limit = intval($this->getProperty('limit'));
		$start = intval($this->getProperty('start'));
		/* query for chunks */
		$c = $this->modx->newQuery($this->classKey);
		$c = $this->prepareQueryBeforeCount($c);
		$data['total'] = $this->modx->getCount($this->classKey,$c);
		$c = $this->prepareQueryAfterCount($c);
		$sortClassKey = $this->getSortClassKey();
		$sortKey = $this->modx->getSelectColumns($sortClassKey,$this->getProperty('sortAlias',$sortClassKey),'',array($this->getProperty('sort')));
		if (empty($sortKey)) $sortKey = $this->getProperty('sort');
		$c->sortby($sortKey,$this->getProperty('dir'));
		if ($limit > 0) {
			$c->limit($limit,$start);
		}
		if ($c->prepare() && $c->stmt->execute()) {
			$data['results'] = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		return $data;
	}
	/** {@inheritDoc} */
	public function iterate(array $data) {
		$list = array();
		$list = $this->beforeIteration($list);
		$this->currentIndex = 0;
		/** @var xPDOObject|modAccessibleObject $object */
		foreach ($data['results'] as $array) {
			$list[] = $this->prepareArray($array);
			$this->currentIndex++;
		}
		$list = $this->afterIteration($list);
		return $list;
	}
	/** {@inheritDoc} */
	public function prepareArray(array $data) {

		return $data;
	}

}

return 'vpPluginEventGetListProcessor';