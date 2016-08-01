<?php

class vpEventUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'vpEvent';
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

    /** {@inheritDoc} */
    public function beforeSet()
    {
        if ($this->modx->getCount('vpEvent', array(
            'id:!=' => $this->getProperty('id'),
            'name'  => $this->getProperty('name'),
        ))
        ) {
            $this->modx->error->addField('name', $this->modx->lexicon('virtualpage_err_ae'));
        }

        return parent::beforeSet();
    }

    /** {@inheritDoc} */
    public function afterSave()
    {
        $this->virtualpage->clearAllCache();

        return parent::afterSave();
    }
}

return 'vpEventUpdateProcessor';