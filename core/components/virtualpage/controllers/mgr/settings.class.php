<?php

require_once dirname(dirname(dirname(__FILE__))) . '/index.class.php';

class ControllersSettingsManagerController extends virtualpageMainController {

	public static function getDefaultController() {
		return 'settings';
	}

}

class virtualpageSettingsManagerController extends virtualpageMainController {

	public function getPageTitle() {
		return $this->modx->lexicon('virtualpage') . ' :: ' . $this->modx->lexicon('vp_settings');
	}

	public function getLanguageTopics() {
		return array('virtualpage:default');
	}

	public function loadCustomCssJs() {
		$this->addJavascript(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');

		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/misc/utils.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/misc/vp.combo.js');

		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/settings/route.window.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/settings/route.grid.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/settings/handler.window.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/settings/handler.grid.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/settings/event.grid.js');
		$this->addJavascript($this->virtualpage->config['jsUrl'] . 'mgr/settings/settings.panel.js');

		$this->addHtml(str_replace('			', '', '
			<script type="text/javascript">
				Ext.onReady(function() {
					MODx.load({ xtype: "virtualpage-page-settings"});
				});
			</script>'
		));
	}

	public function getTemplateFile() {
		return $this->virtualpage->config['templatesPath'] . 'mgr/settings.tpl';
	}

}

// MODX 2.3
class ControllersMgrSettingsManagerController extends ControllersSettingsManagerController {

	public static function getDefaultController() {
		return 'mgr/settings';
	}

}

class virtualpageMgrSettingsManagerController extends virtualpageSettingsManagerController {

}
