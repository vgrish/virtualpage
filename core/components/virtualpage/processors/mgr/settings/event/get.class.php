<?php

class vpEventGetProcessor extends modObjectGetProcessor
{
	public $objectType = 'vpEvent';
	public $classKey = 'vpEvent';
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

return 'vpEventGetProcessor';