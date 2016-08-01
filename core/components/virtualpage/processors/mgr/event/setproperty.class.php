<?php
require_once dirname(__FILE__) . '/update.class.php';

/**
 * SetProperty a vpEvent
 */
class vpEventSetPropertyProcessor extends vpEventUpdateProcessor
{
    /** @var vpEvent $object */
    public $object;
    public $objectType = 'vpEvent';
    public $classKey = 'vpEvent';
    public $languageTopics = array('virtualpage');
    public $permission = '';

    /** {@inheritDoc} */
    public function beforeSet()
    {
        $fieldName = $this->getProperty('field_name', null);
        $fieldValue = $this->getProperty('field_value', null);

        $this->properties = array();
        if (!is_null($fieldName) AND !is_null($fieldValue)) {
            $this->setProperty($fieldName, $fieldValue);
        }

        return parent::beforeSet();
    }

}

return 'vpEventSetPropertyProcessor';