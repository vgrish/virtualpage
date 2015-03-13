<?php
class vpRouteGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'vpRoute';
	public $defaultSortField = 'rank';
	public $defaultSortDirection  = 'asc';
	public $permission = '';
	/** {@inheritDoc} */
	public function initialize() {
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}
		return parent::initialize();
	}
	/** {@inheritDoc} */
	public function prepareQueryBeforeCount(xPDOQuery $c) {

		$c->leftJoin('vpEvent','vpEvent', '`vpRoute`.`event` = `vpEvent`.`id`');
		$routeColumns = $this->modx->getSelectColumns('vpRoute', 'vpRoute', '', array(), true);
		$c->select($routeColumns . ', `vpEvent`.`name` as `event_name`');

		if ($active = $this->getProperty('active')) {
			$c->where(array('active' => $active));
		}
		if ($this->getProperty('combo')) {
			$c->select('id,name');
			$c->where(array('active' => 1));
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
		$data['active'] = (int) $data['active'];

		return $data;
	}
}
return 'vpRouteGetListProcessor';