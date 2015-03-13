<?php
class vpEvent extends xPDOSimpleObject {

	/** {@inheritdoc} */
	public function remove(array $ancestors= array ()) {
		$eventName = $this->get('name');
		$this->xpdo->virtualpage->doEvent('remove', $eventName, 'vpEvent', 10);

		return parent::remove();
	}

}