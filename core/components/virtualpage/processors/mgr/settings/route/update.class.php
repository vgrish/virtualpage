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
		$id = $this->object->get('id');
		$eventId = $this->object->get('event');

		if($event = $this->modx->getObject('vpEvent', $eventId)) {
			$eventName = $event->get('name');
		}
		if(!$route = $this->modx->getObject('vpRoute', array('id:!=' => $id, 'event' => $eventId))) {
			$this->modx->virtualpage->doEvent('remove', $eventName, 'vpEvent', 10);
		}

		return parent::beforeSet();
	}
	/** {@inheritDoc} */
	public function afterSave() {
		$this->modx->virtualpage->clearCache('event');
		$this->modx->virtualpage->clearCache('route');
		$this->modx->virtualpage->clearCache('fastrouter');
		//
		if($event = $this->modx->getObject('vpEvent', $this->object->get('event'))) {
			$eventName = $event->get('name');
			// set event
			$this->modx->virtualpage->doEvent('create', $eventName, 'vpEvent', 10);
		}

		return parent::afterSave();
	}

}
return 'vpRouteUpdateProcessor';