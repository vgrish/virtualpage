<?php


class vpEntryGetListProcessor extends modObjectProcessor
{
    public function process()
    {
        $id = $this->getProperty('id');
        $start = $this->getProperty('start', 0);
        $limit = $this->getProperty('limit', 10);

        $query = $this->getProperty('query');
        $element = $this->getProperty('element');
        switch ($element) {
            case 'chunk':
                $class = 'modChunk';
                $name = 'name';
                break;
            case 'template':
                $class = 'modTemplate';
                $name = 'templatename';
                break;
            case 'snippet':
                $class = 'modSnippet';
                $name = 'name';
                break;
            case 'resource':
                $class = 'modResource';
                $name = 'pagetitle';
                break;
            default:
                $class = '-';
                $name = 'name';
                break;
        }

        if (in_array($class, array(null, '-'))) {
            return $this->outputArray(array());
        }

        $c = $this->modx->newQuery($class);
        $c->sortby($name, 'ASC');
        $c->select("{$name} as name, id as id");
        $c->groupby($name);

        if (
            $this->getProperty('combo')
            AND
            !empty($id)
            AND
            empty($start)
        ) {
            $q = $this->modx->newQuery($class);
            $q->where(array('id!=' => $id));
            $q->select('id');
            $q->limit($this->getProperty('limit', 10) - 1);
            $q->prepare();
            $q->stmt->execute();
            $ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $ids = array_merge_recursive(array($id), $ids);
            $c->where(array(
                "{$class}.id:IN" => $ids
            ));
        }

        $c->limit(0);
        if (!empty($query)) {
            $c->where(array("{$name}:LIKE" => "%{$query}%"));
        }
        if ($c->prepare() && $c->stmt->execute()) {
            $array = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $array = array();
        }

        $count = $this->modx->getCount($class);
        $array = array_slice($array, $start, $limit);

        return $this->outputArray($array, $count);
    }

    /** {@inheritDoc} */
    public function outputArray(array $array, $count = false)
    {
        if ($this->getProperty('novalue')) {
            $array = array_merge_recursive(array(
                array(
                    'id'   => 0,
                    'name' => $this->modx->lexicon('virtualpage_no')
                )
            ), $array);
        }
        if ($this->getProperty('addall')) {
            $array = array_merge_recursive(array(
                array(
                    'id'   => '-',
                    'name' => $this->modx->lexicon('virtualpage_all')
                )
            ), $array);
        }

        return parent::outputArray($array, $count);
    }

}

return 'vpEntryGetListProcessor';