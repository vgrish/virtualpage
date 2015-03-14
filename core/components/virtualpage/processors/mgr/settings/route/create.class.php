<?php
class vpRouteCreateProcessor extends modObjectCreateProcessor {
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
		if ($this->modx->getObject('vpRoute',array('route' => $this->getProperty('route')))) {
			$this->modx->error->addField('route', $this->modx->lexicon('vp_err_ae'));
		}
		return !$this->hasErrors();
	}
	/** {@inheritDoc} */
	public function beforeSave() {
		$this->object->fromArray(array(
			'rank' => $this->modx->getCount('vpRoute')
		));
		return parent::beforeSave();
	}
	/** {@inheritDoc} */
	public function afterSave() {
		$eventId = $this->object->get('event');
		if($event = $this->modx->getObject('vpEvent', $eventId)) {
			$eventName = $event->get('name');
			// set event
			$this->modx->virtualpage->doEvent('create', $eventName, 'vpEvent', 10);
		}

		return parent::afterSave();
	}
}
return 'vpRouteCreateProcessor';