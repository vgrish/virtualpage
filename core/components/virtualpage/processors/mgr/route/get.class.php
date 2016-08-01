<?php

class vpRouteGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'vpRoute';
    public $classKey = 'vpRoute';
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

return 'vpRouteGetProcessor';