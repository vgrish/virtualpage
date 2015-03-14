<?php

/**
 * The base class for virtualpage.
 */
class virtualpage {
	/* @var modX $modx */
	public $modx;

	/** @var array $initialized */
	public $initialized = array();

	public $namespace = 'virtualpage';
	public $config = array();
	public $active = false;

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;

		$this->namespace = $this->getOption('namespace', $config, 'virtualpage');
		$corePath = $this->modx->getOption('virtualpage_core_path', $config, $this->modx->getOption('core_path') . 'components/virtualpage/');
		$assetsUrl = $this->modx->getOption('virtualpage_assets_url', $config, $this->modx->getOption('assets_url') . 'components/virtualpage/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'imagesUrl' => $assetsUrl . 'images/',
			'connectorUrl' => $connectorUrl,

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/',

			'cache_key' => $this->namespace.'/',

		), $config);

		$this->modx->addPackage('virtualpage', $this->config['modelPath']);
		$this->modx->lexicon->load('virtualpage:default');

		$this->active = $this->modx->getOption('virtualpage_active', $config, false);

	}

	/**
	 * @param $key
	 * @param array $config
	 * @param null $default
	 * @return mixed|null
	 */
	public function getOption($key, $config = array(), $default = null) {
		$option = $default;
		if (!empty($key) && is_string($key)) {
			if ($config != null && array_key_exists($key, $config)) {
				$option = $config[$key];
			} elseif (array_key_exists($key, $this->config)) {
				$option = $this->config[$key];
			} elseif (array_key_exists("{$this->namespace}.{$key}", $this->modx->config)) {
				$option = $this->modx->getOption("{$this->namespace}.{$key}");
			}
		}
		return $option;
	}

	/**
	 * Shorthand for load and run an processor in this component
	 *
	 * @param string $action
	 * @param array $scriptProperties
	 *
	 * @return mixed
	 */
	function runProcessor($action = '', $scriptProperties = array()) {
		$this->modx->error->errors = $this->modx->error->message = null;
		return $this->modx->runProcessor($action, $scriptProperties, array(
				'processors_path' => $this->config['processorsPath']
			)
		);
	}

	public function doRoutes($sp = array())
	{
		$eventName = $sp['eventName'];
		$routes = $sp['routes'];


		$this->modx->log(1, print_r('зашли в - '.$eventName, 1));

		if (empty($routes)) {
			$this->modx->log(1, print_r('[vp]:Error empty routes for event - ' . $eventName, 1));
			return false;
		}

		//
		foreach($routes as $n => $v) {

			$this->modx->log(1 , print_r('i- '. $n  ,1));


			if(!$route = $this->modx->getObject('vpRoute', array('id' => $n, 'active' => 1))) {continue;}

		}
		return true;
	}

	/**
	 * set/remove Event to Plugin
	 *
	 * @param string $action
	 * @param string $nameEvent
	 * @param string $namePlugin
	 * @param int $priority
	 * @return bool
	 */
	public function doEvent($action = 'create', $nameEvent = '', $namePlugin = 'vpEvent', $priority = 0)
	{
		if (empty($nameEvent)) return false;
		if ($plugin = $this->modx->getObject('modPlugin', array('name' => $namePlugin))) {
			$id = $plugin->get('id');
			// clear cache
			$this->clearCache();
			// create || update
			if (($action == 'create') || ($action == 'update')) {
				if (!$event = $this->modx->getObject('modPluginEvent', array('pluginid' => $id, 'event' => $nameEvent))) {
					$event = $this->modx->newObject('modPluginEvent');
				}
				$event->set('pluginid', $id);
				$event->set('event', $nameEvent);
				$event->set('priority', $priority);
				if ($event->save()) {
					$this->modx->cacheManager->refresh();
					return true;
				}
			}
			else {
				//remove
				if ($event = $this->modx->getObject('modPluginEvent', array('pluginid' => $id, 'event' => $nameEvent))) {
					if ($event->remove()) {
						$this->modx->cacheManager->refresh();
						return true;
					}
				}
			}
			return true;
		}
		$this->modx->log(1, print_r('[virtualpage]:Error get modPlugin - ' . $namePlugin, 1));
		return false;
	}

	/**
	 * return array() Event
	 *
	 * @return mixed
	 */
	public function getEvents()
	{
		$key = 'event';
		$cacheKey = $this->config['cache_key'].$key;
		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$ListEvent = $this->modx->getCacheManager()->get($key, $cacheOptions);
		//
		if (empty($ListEvent) && $this->modx->getCount('vpEvent') > 0) {
			$data['active'] = 1;
			$tmp = $this->runProcessor('mgr/settings/event/getlist', $data);
			if ($response = json_decode($tmp->response, 1)) {
				foreach ($response['results'] as $v) {
					if(empty($v['routes'])) {continue;}
					$ListEvent[$v['name']] = $v['routes'];
				}
			}
			$this->modx->cacheManager->set($key, $ListEvent, 0, $cacheOptions);
		}
		//
		return $ListEvent;
	}

	/**
	 * clear cache for $key
	 *
	 * @param string $key
	 */
	public function clearCache($key = 'event')
	{
		$cacheKey = $this->config['cache_key'].$key;
		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$this->modx->cacheManager->clean($cacheOptions);
	}

	/**
	 * @param $sp
	 */
	public function OnHandleRequest($sp)
	{
		// get events
		$this->getEvents();
	}

}