<?php

class modSnippetGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'modSnippet';
    public $languageTopics = array('snippet', 'category');
    public $defaultSortField = 'name';

    /** {@inheritDoc} */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        if ($this->getProperty('combo')) {
            $c->select('id,name');
        }
        $query = $this->getProperty('query');
        if (!empty($query)) {
            $c->where(array('name:LIKE' => '%' . $query . '%'));
        }

        return $c;
    }

    /** {@inheritDoc} */
    public function prepareRow(xPDOObject $object)
    {
        if ($this->getProperty('combo')) {
            $array = array(
                'id'   => $object->get('id'),
                'name' => '(' . $object->id . ') ' . $object->get('name')
            );
        } else {
            $array = $object->toArray();
        }

        return $array;
    }
}

return 'modSnippetGetListProcessor';