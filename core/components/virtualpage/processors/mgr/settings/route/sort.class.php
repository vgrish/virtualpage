<?php

// It is adapted code from https://github.com/splittingred/Gallery/blob/a51442648fde1066cf04d46550a04265b1ad67da/core/components/gallery/processors/mgr/item/sort.php
class vpRouteSortProcessor extends modObjectProcessor
{
    public $classKey = 'vpRoute';
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
    public function process()
    {
        /* @var vpRoute $source */
        $source = $this->modx->getObject($this->classKey, $this->getProperty('source'));
        /* @var vpRoute $target */
        $target = $this->modx->getObject($this->classKey, $this->getProperty('target'));
        if (empty($source) || empty($target)) {
            return $this->modx->error->failure();
        }
        if ($source->get('rank') < $target->get('rank')) {
            $this->modx->exec("UPDATE {$this->modx->getTableName($this->classKey)}
				SET rank = rank - 1 WHERE
					rank <= {$target->get('rank')}
					AND rank > {$source->get('rank')}
					AND rank > 0
			");
        } else {
            $this->modx->exec("UPDATE {$this->modx->getTableName($this->classKey)}
				SET rank = rank + 1 WHERE
					rank >= {$target->get('rank')}
					AND rank < {$source->get('rank')}
			");
        }
        $newRank = $target->get('rank');
        $source->set('rank', $newRank);
        $source->save();
        if (!$this->modx->getCount($this->classKey, array('rank' => 0))) {
            $this->setRanks();
        }

        $this->virtualpage->clearAllCache();

        return $this->modx->error->success();
    }

    /** {@inheritDoc} */
    public function setRanks()
    {
        $q = $this->modx->newQuery($this->classKey);
        $q->select('id');
        $q->sortby('rank ASC, id', 'ASC');
        if ($q->prepare() && $q->stmt->execute()) {
            $ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
            $sql = '';
            $table = $this->modx->getTableName($this->classKey);
            foreach ($ids as $k => $id) {
                $sql .= "UPDATE {$table} SET `rank` = '{$k}' WHERE `id` = '{$id}';";
            }
            $this->modx->exec($sql);
        }
    }
}

return 'vpRouteSortProcessor';