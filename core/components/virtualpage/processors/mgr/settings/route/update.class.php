<?php
class vpRouteUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'vpRoute';
	public $languageTopics = array('virtualpage');
	public $permission = 'vpsetting_save';

	public $nameRoute;

	/** {@inheritDoc} */
	public function initialize() {
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}
		if ($Event = $this->modx->getObject('vpRoute', $this->getProperty('id'))) {
			$this->nameRoute = $Event->get('name');
		}

		return parent::initialize();
	}
	/** {@inheritDoc} */
	public function beforeSet() {
		if ($this->modx->getObject('vpRoute',array('name' => $this->getProperty('name'), 'id:!=' => $this->getProperty('id') ))) {
			$this->modx->error->addField('name', $this->modx->lexicon('vp_err_ae'));
		}

		return parent::beforeSet();
	}
	/** {@inheritDoc} */
	public function afterSave() {
		$this->modx->virtualpage->clearCache();

		return parent::afterSave();
	}

}
return 'vpRouteUpdateProcessor';