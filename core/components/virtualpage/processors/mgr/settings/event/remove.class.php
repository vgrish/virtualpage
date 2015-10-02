<?php

class vpEventRemoveProcessor extends modObjectRemoveProcessor
{
	public $classKey = 'vpEvent';
	public $languageTopics = array('virtualpage');
	public $permission = 'vpsetting_save';

	/** {@inheritDoc} */
	public function initialize()
	{
		if (!$this->modx->hasPermission($this->permission)) {
			return $this->modx->lexicon('access_denied');
		}

		return parent::initialize();
	}

	public function afterRemove()
	{

		return parent::afterRemove();
	}

}

return 'vpEventRemoveProcessor';