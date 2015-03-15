<?php
class vpRouteUpdateProcessor extends modObjectUpdateProcessor {
	public $classKey = 'vpRoute';
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
/*		if ($this->modx->getObject('vpRoute',array('name' => $this->getProperty('name'), 'id:!=' => $this->getProperty('id') ))) {
			$this->modx->error->addField('name', $this->modx->lexicon('vp_err_ae'));
		}*/

		return parent::beforeSet();
	}
	/** {@inheritDoc} */
	public function afterSave() {
		$this->modx->virtualpage->clearCache('event');
		$this->modx->virtualpage->clearCache('route');
		$this->modx->virtualpage->clearCache('fastrouter');

		return parent::afterSave();
	}

}
return 'vpRouteUpdateProcessor';