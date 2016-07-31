<?php

class vpHandlerUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'vpHandler';
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

    /** {@inheritDoc} */
    public function beforeSet()
    {
        if ($this->modx->getCount('vpHandler', array(
            'id:!=' => $this->getProperty('id'),
            'name'  => $this->getProperty('name'),
        ))
        ) {
            $this->modx->error->addField('name', $this->modx->lexicon('vp_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'vpHandlerUpdateProcessor';