<?php


class virtualpageOnPageNotFound extends virtualpagePlugin
{
    public function run()
    {
        /* check context */
        if ($this->modx->context->key == 'mgr') {
            return;
        }
        /* check site status */
        if (
            $this->modx->getOption('virtualpage_ischeck_site_status', null, true, true)
            AND
            !$this->modx->checkSiteStatus()
        ) {
            return;
        }

        /* check virtualpage status */
        if (
            !$this->virtualpage->active
            OR
            isset($_REQUEST['virtualpage_die'])
            OR
            isset($this->modx->event->params['virtualpage_die'])
        ) {
            return;
        }

        $this->virtualpage->dispatch($this->modx->event->name);
    }
}