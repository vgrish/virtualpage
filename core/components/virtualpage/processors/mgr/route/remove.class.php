<?php

class vpRouteRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'vpRoute';
    public $languageTopics = array('virtualpage');
    public $permission = 'vpsetting_save';

    /** @var virtualpage $virtualpage */
    public $virtualpage;

    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }
        $this->virtualpage = $this->modx->getService('virtualpage');

        return parent::initialize();
    }

    public function afterRemove()
    {
        $this->virtualpage->clearAllCache();

        return parent::afterRemove();
    }

}

return 'vpRouteRemoveProcessor';