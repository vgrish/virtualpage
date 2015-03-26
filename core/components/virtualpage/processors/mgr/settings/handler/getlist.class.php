<?php
class vpHandlerGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'vpHandler';
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

		switch($array['type']) {
			case 0: {
				$n = 'vp_type_resource';
				break;
			}
			case 1: {
				$n = 'vp_type_snippet';
				break;
			}
			case 2: {
				$n = 'vp_type_chunk';
				break;
			}

		}
		$array['name_type'] = $this->modx->lexicon($n);

		return $array;
	}
}
return 'vpHandlerGetListProcessor';