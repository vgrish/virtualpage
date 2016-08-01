<?php

/**
 * Get a list of vpHandler
 */
class vpHandlerGetListProcessor extends modObjectGetListProcessor
{
    public $objectType = 'vpHandler';
    public $classKey = 'vpHandler';
    public $defaultSortField = 'rank';
    public $defaultSortDirection = 'ASC';
    public $languageTopics = array('default', 'virtualpage');
    public $permission = '';

    /**
     * @param xPDOQuery $c
     *
     * @return xPDOQuery
     */
    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $id = $this->getProperty('id');
        if (!empty($id) AND $this->getProperty('combo')) {
            $q = $this->modx->newQuery($this->classKey);
            $q->where(array('id!=' => $id));
            $q->select('id');
            $q->limit($this->getProperty('limit') - 1);
            $q->prepare();
            $q->stmt->execute();
            $ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $ids = array_merge_recursive(array($id), $ids);
            $c->where(array(
                "{$this->classKey}.id:IN" => $ids
            ));
        }

        $c->select($this->modx->getSelectColumns($this->classKey, $this->classKey));

        $active = $this->getProperty('active');
        if (!in_array($active, array(null, '-'))) {
            $c->where("{$this->objectType}.active={$active}");
        }

        $query = trim($this->getProperty('query'));
        if ($query) {
            $c->where(array(
                'name:LIKE'           => "%{$query}%",
                'OR:description:LIKE' => "%{$query}%",
            ));
        }

        $c->groupby("{$this->classKey}.id");

        return $c;
    }

    /** {@inheritDoc} */
    public function outputArray(array $array, $count = false)
    {
        if ($this->getProperty('addall')) {
            $array = array_merge_recursive(array(
                array(
                    'id'   => 0,
                    'name' => $this->modx->lexicon('virtualpage_all')
                )
            ), $array);
        }
        if ($this->getProperty('novalue')) {
            $array = array_merge_recursive(array(
                array(
                    'id'   => 0,
                    'name' => $this->modx->lexicon('virtualpage_no')
                )
            ), $array);
        }

        return parent::outputArray($array, $count);
    }


    /**
     * @param xPDOObject $object
     *
     * @return array
     */
    public function prepareArray(array $array)
    {
        if ($this->getProperty('combo')) {


        } else {

            $icon = 'icon';
            $array['actions'] = array();

            // Edit
            $array['actions'][] = array(
                'cls'    => '',
                'icon'   => "$icon $icon-edit green",
                'title'  => $this->modx->lexicon('virtualpage_action_update'),
                'action' => 'update',
                'button' => true,
                'menu'   => true,
            );

            // sep
            $array['actions'][] = array(
                'cls'    => '',
                'icon'   => '',
                'title'  => '',
                'action' => 'sep',
                'button' => false,
                'menu'   => true,
            );

            if (!$array['active']) {
                $array['actions'][] = array(
                    'cls'    => '',
                    'icon'   => "$icon $icon-toggle-off red",
                    'title'  => $this->modx->lexicon('virtualpage_action_turnon'),
                    'action' => 'active',
                    'button' => true,
                    'menu'   => true,
                );
            } else {
                $array['actions'][] = array(
                    'cls'    => '',
                    'icon'   => "$icon $icon-toggle-on green",
                    'title'  => $this->modx->lexicon('virtualpage_action_turnoff'),
                    'action' => 'inactive',
                    'button' => true,
                    'menu'   => true,
                );
            }

            if (!$array['cache']) {
                $array['actions'][] = array(
                    'cls'    => '',
                    'icon'   => "$icon $icon-refresh red",
                    'title'  => $this->modx->lexicon('virtualpage_action_cacheon'),
                    'action' => 'cacheon',
                    'button' => true,
                    'menu'   => true,
                );
            } else {
                $array['actions'][] = array(
                    'cls'    => '',
                    'icon'   => "$icon $icon-refresh green",
                    'title'  => $this->modx->lexicon('virtualpage_action_cacheoff'),
                    'action' => 'cacheoff',
                    'button' => true,
                    'menu'   => true,
                );
            }


            // Remove
            $array['actions'][] = array(
                'cls'    => '',
                'icon'   => "$icon $icon-trash-o red",
                'title'  => $this->modx->lexicon('virtualpage_action_remove'),
                'action' => 'remove',
                'button' => true,
                'menu'   => true,
            );

            switch ($array['type']) {
                case 0: {
                    $n = 'virtualpage_type_resource';
                    break;
                }
                case 1: {
                    $n = 'virtualpage_type_snippet';
                    break;
                }
                case 2: {
                    $n = 'virtualpage_type_chunk';
                    break;
                }
                case 3: {
                    $n = 'virtualpage_type_dynamic_resource';
                    break;
                }

            }
            $array['name_type'] = $this->modx->lexicon($n);

        }

        return $array;
    }

    /**
     * Get the data of the query
     * @return array
     */
    public function getData()
    {
        $data = array();
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));

        $c = $this->modx->newQuery($this->classKey);
        $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey, $c);
        $c = $this->prepareQueryAfterCount($c);

        $sortClassKey = $this->getSortClassKey();
        $sortKey = $this->modx->getSelectColumns($sortClassKey, $this->getProperty('sortAlias', $sortClassKey), '',
            array($this->getProperty('sort')));
        if (empty($sortKey)) {
            $sortKey = $this->getProperty('sort');
        }
        $c->sortby($sortKey, $this->getProperty('dir'));
        if ($limit > 0) {
            $c->limit($limit, $start);
        }

        if ($c->prepare() AND $c->stmt->execute()) {
            $data['results'] = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function iterate(array $data)
    {
        $list = array();
        $list = $this->beforeIteration($list);
        $this->currentIndex = 0;
        /** @var xPDOObject|modAccessibleObject $object */
        foreach ($data['results'] as $array) {
            $list[] = $this->prepareArray($array);
            $this->currentIndex++;
        }
        $list = $this->afterIteration($list);

        return $list;
    }

}

return 'vpHandlerGetListProcessor';