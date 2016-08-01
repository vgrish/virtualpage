<?php

class vpRouteUpdateProcessor extends modObjectUpdateProcessor
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

    /** {@inheritDoc} */
    public function beforeSet()
    {
        if ($this->modx->getCount('vpRoute', array(
            'id:!=' => $this->getProperty('id'),
            'route' => $this->getProperty('route'),
            'metod' => $this->getProperty('metod'),
        ))
        ) {
            $this->modx->error->addField('route', $this->modx->lexicon('virtualpage_err_ae'));
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

return 'vpRouteUpdateProcessor';