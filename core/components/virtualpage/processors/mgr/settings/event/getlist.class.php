<?php
class vpEventGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'vpEvent';
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
	public function prepareRow(xPDOObject $object) {
		$array = $object->toArray();

		return $array;
	}
}
return 'vpEventGetListProcessor';