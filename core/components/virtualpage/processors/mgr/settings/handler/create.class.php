<?php
class vpHandlerCreateProcessor extends modObjectCreateProcessor {
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
		if ($this->modx->getObject('vpHandler',array('name' => $this->getProperty('name')))) {
			$this->modx->error->addField('name', $this->modx->lexicon('vp_err_ae'));
		}
		$type = $this->getProperty('type');
		if($resource = $this->getProperty('resource')) {
			if ($this->modx->getObject('vpHandler',array('type' => $type, 'resource' => $resource ))) {
				$this->modx->error->addField('resource', $this->modx->lexicon('vp_err_ae'));
			}
		}
		if($snippet = $this->getProperty('snippet')) {
			if ($this->modx->getObject('vpHandler',array('type' => $type, 'snippet' => $snippet ))) {
				$this->modx->error->addField('snippet', $this->modx->lexicon('vp_err_ae'));
			}
		}

		return !$this->hasErrors();
	}
	/** {@inheritDoc} */
	public function beforeSave() {
		$this->object->fromArray(array(
			'rank' => $this->modx->getCount('vpHandler')
		));
		return parent::beforeSave();
	}

}
return 'vpHandlerCreateProcessor';