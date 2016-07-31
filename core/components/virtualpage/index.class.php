<?php

/**
 * Class virtualpageMainController
 */
abstract class virtualpageMainController extends modExtraManagerController
{
    /** @var virtualpage $virtualpage */
    public $virtualpage;


    /**
     * @return void
     */
    public function initialize()
    {
        $corePath = $this->modx->getOption('virtualpage_core_path', null,
            $this->modx->getOption('core_path') . 'components/virtualpage/');
        require_once $corePath . 'model/virtualpage/virtualpage.class.php';

        $this->virtualpage = new virtualpage($this->modx);
        $this->addCss($this->virtualpage->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/virtualpage.js');
        $this->addHtml('
		<script type="text/javascript">
			virtualpage.config = ' . $this->modx->toJSON($this->virtualpage->config) . ';
			virtualpage.config.connector_url = "' . $this->virtualpage->config['connectorUrl'] . '";
		</script>
		');

        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array('virtualpage:default');
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends virtualpageMainController
{

    /**
     * @return string
     */
    public static function getDefaultController()
    {
        return 'mgr/settings';
    }
}