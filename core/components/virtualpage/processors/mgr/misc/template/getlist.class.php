<?php

class modTemplateGetListProcessor extends modObjectGetListProcessor {
	public $classKey = 'modTemplate';
	public $languageTopics = array('template','category');
	public $defaultSortField = 'templatename';
	/** {@inheritDoc} */
	public function prepareQueryBeforeCount(xPDOQuery $c) {
		if ($this->getProperty('combo')) {
			$c->select('id,templatename');
		}
		$query = $this->getProperty('query');
		if (!empty($query)) {
			$c->where(array('templatename:LIKE' => '%'.$query.'%'));
		}
		return $c;
	}
	/** {@inheritDoc} */
	public function prepareRow(xPDOObject $object) {
		if ($this->getProperty('combo')) {
			$array = array(
				'id' => $object->get('id'),
				'name' => '('.$object->id.') ' . $object->get('templatename')
			);
		}
		else {
			$array = $object->toArray();
		}
		return $array;
	}
}
return 'modTemplateGetListProcessor';