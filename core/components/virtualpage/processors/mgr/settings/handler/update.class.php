<?php
class vpHandlerUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'vpHandler';
	public $languageTopics = array('virtualpage');
	public $permission = 'vpsetting_save';

	/** {@inheritDoc} */
	public function initialize() {
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}

		return parent::initialize();
	}
	/** {@inheritDoc} */
	public function beforeSet() {
		if ($this->modx->getObject('vpHandler',array('name' => $this->getProperty('name'), 'id:!=' => $this->getProperty('id') ))) {
			$this->modx->error->addField('name', $this->modx->lexicon('vp_err_ae'));
		}
		if ($this->modx->getObject('vpHandler',array(
			'type' => $this->getProperty('type'),
			'entry' => $this->getProperty('entry'),
			'id:!=' => $this->getProperty('id')
		))) {
			$this->modx->error->addField('entry', $this->modx->lexicon('vp_err_ae'));
		}

		return parent::beforeSet();
	}

}
return 'vpHandlerUpdateProcessor';