<?php

/**
 * The home manager controller for virtualpage.
 *
 */
class virtualpageHomeManagerController extends virtualpageMainController {
	/* @var virtualpage $virtualpage */
	public $virtualpage;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('virtualpage');
	}


	/**
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addCss($this->virtualpage->config['cssUrl'] . 'mgr/main.css');
		$this->addCss($this->virtualpage->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/misc/utils.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/widgets/items.windows.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/sections/home.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "virtualpage-page-home"});
		});
		</script>');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->virtualpage->config['templatesPath'] . 'home.tpl';
	}
}