<?php

class vpHandlerCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'vpHandler';
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
        if ($this->modx->getCount('vpHandler', array(
            'name' => $this->getProperty('name')
        ))
        ) {
            $this->modx->error->addField('name', $this->modx->lexicon('virtualpage_err_ae'));
        }

        return !$this->hasErrors();
    }

    /** {@inheritDoc} */
    public function beforeSave()
    {
        $this->object->fromArray(array(
            'rank' => $this->modx->getCount('vpHandler')
        ));

        return parent::beforeSave();
    }

    /** {@inheritDoc} */
    public function afterSave()
    {
        $this->virtualpage->clearAllCache();

        return parent::afterSave();
    }

}

return 'vpHandlerCreateProcessor';