<?php

class vpRouteCreateProcessor extends modObjectCreateProcessor
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
            'route' => $this->getProperty('route'),
            'metod' => $this->getProperty('metod'),
        ))
        ) {
            $this->modx->error->addField('route', $this->modx->lexicon('virtualpage_err_ae'));
        }

        return !$this->hasErrors();
    }

    /** {@inheritDoc} */
    public function beforeSave()
    {
        $this->object->fromArray(array(
            'rank' => $this->modx->getCount('vpRoute')
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

return 'vpRouteCreateProcessor';