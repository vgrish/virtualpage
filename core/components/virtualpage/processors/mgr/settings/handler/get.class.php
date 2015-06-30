<?php

class vpHandlerGetProcessor extends modObjectGetProcessor
{
	public $objectType = 'vpHandler';
	public $classKey = 'vpHandler';
	public $languageTopics = array('virtualpage:manager');

	/**
	 * @return array|string
	 */
	public function cleanup()
	{
		$set = $this->object->toArray();

		return $this->success('', $set);
	}

}

return 'vpHandlerGetProcessor';