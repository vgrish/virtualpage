<?php

class vpEntryGetListProcessor extends modObjectProcessor
{
    public $classKey = 'vpHandler';

    /** {@inheritDoc} */
    public function process()
    {

        $element = $this->getProperty('element', 'resource');
        $query = $this->getProperty('query');

        if (!$response = $this->modx->runProcessor('getlist',
            array(
                'combo' => true,
                'query' => $query,
            ),
            array('processors_path' => dirname(dirname(__FILE__)) . '/' . $element . '/')
        )
        ) {
            $this->modx->log(1, print_r('[virtualpage]:Error get element -  ' . $element, 1));

            return $this->success();
        }
        $result = $this->modx->fromJSON($response->getResponse());
        if (!empty($result['results'])) {
            foreach ($result['results'] as $k => $v) {
                if (isset($result['results'][$k]['name'])) {
                    continue;
                }
                $result['results'][$k]['name'] = $result['results'][$k]['pagetitle'];
            }
        }

        return $this->modx->toJSON($result);
    }
}

return 'vpEntryGetListProcessor';