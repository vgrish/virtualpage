<?php

class vpEventUpdateProcessor extends modObjectUpdateProcessor
{
	public $classKey = 'vpEvent';
	public $languageTopics = array('virtualpage');
	public $permission = 'vpsetting_save';

	public $nameEvent;

	/** {@inheritDoc} */
	public function initialize()
	{
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}
		if ($Event = $this->modx->getObject('vpEvent', $this->getProperty('id'))) {
			$this->nameEvent = $Event->get('name');
		}

		return parent::initialize();
	}

	/** {@inheritDoc} */
	public function beforeSet()
	{
		if ($this->modx->getObject('vpEvent', array('name' => $this->getProperty('name'), 'id:!=' => $this->getProperty('id')))) {
			$this->modx->error->addField('name', $this->modx->lexicon('vp_err_ae'));
		}

		return parent::beforeSet();
	}

}

return 'vpEventUpdateProcessor';