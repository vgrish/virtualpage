<?php

/**
 * Get an Item
 */
class virtualpageItemGetProcessor extends modObjectGetProcessor {
	public $objectType = 'virtualpageItem';
	public $classKey = 'virtualpageItem';
	public $languageTopics = array('virtualpage:default');
	//public $permission = 'view';


	/**
	 * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return mixed
	 */
	public function process() {
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		return parent::process();
	}

}

return 'virtualpageItemGetProcessor';