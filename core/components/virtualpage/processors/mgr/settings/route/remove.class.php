<?php

class vpRouteRemoveProcessor extends modObjectRemoveProcessor
{
	public $classKey = 'vpRoute';
	public $languageTopics = array('virtualpage');
	public $permission = 'vpsetting_save';

	public $eventName;

	/** {@inheritDoc} */
	public function initialize()
	{
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}
		if ($event = $this->modx->getObject('vpEvent', array('id' => $this->getProperty('event')))) {
			$this->eventName = $event->get('name');
		}

		return parent::initialize();
	}

	public function afterRemove()
	{
		if (!$this->modx->getCount('vpRoute', array('event' => $this->getProperty('event')))) {
			/* remove event */
			$this->modx->virtualpage->doEvent('remove', $this->eventName, 'vpEvent', 10);
		}

		return parent::afterRemove();
	}

}

return 'vpRouteRemoveProcessor';