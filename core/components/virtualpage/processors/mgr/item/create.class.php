<?php

/**
 * Create an Item
 */
class virtualpageItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'virtualpageItem';
	public $classKey = 'virtualpageItem';
	public $languageTopics = array('virtualpage');
	//public $permission = 'create';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$name = trim($this->getProperty('name'));
		if (empty($name)) {
			$this->modx->error->addField('name', $this->modx->lexicon('virtualpage_item_err_name'));
		}
		elseif ($this->modx->getCount($this->classKey, array('name' => $name))) {
			$this->modx->error->addField('name', $this->modx->lexicon('virtualpage_item_err_ae'));
		}

		return parent::beforeSet();
	}

}

return 'virtualpageItemCreateProcessor';