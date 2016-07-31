<?php


abstract class virtualpagePlugin
{
    /** @var modX $modx */
    protected $modx;
    /** @var virtualpage $virtualpage */
    protected $virtualpage;
    /** @var array $scriptProperties */
    protected $scriptProperties;

    public function __construct($modx, &$scriptProperties)
    {
        /** @var modX $modx */
        $this->modx = $modx;
        $this->scriptProperties =& $scriptProperties;

        $fqn = $modx->getOption('virtualpage_class', null, 'virtualpage.virtualpage', true);
        $path = $modx->getOption('virtualpage_class_path', null,
            $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/virtualpage/');
        $this->virtualpage = $modx->getService(
            $fqn,
            '',
            $path . 'model/',
            $this->scriptProperties
        );

        if (!$this->virtualpage) {
            return false;
        }
    }

    abstract public function run();
}