<?php

/**
 * The base class for virtualpage.
 */

include_once dirname(dirname(__FILE__)) . '/lib/fastroute/src/bootstrap.php';

class virtualpage {
	/* @var modX $modx */
	public $modx;

	/** @var array $initialized */
	public $initialized = array();

	public $namespace = 'virtualpage';
	public $config = array();
	public $active = false;

	public $event;
	public $routes;
	public $dispatcher;

	public $fastrouterKey;


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
			'fastrouter_cache_key' => 'fastrouter',

		), $config);

		$this->modx->addPackage('virtualpage', $this->config['modelPath']);
		$this->modx->lexicon->load('virtualpage:default');

		$this->active = $this->modx->getOption('virtualpage_active', $config, false);
		$this->fastrouterKey = $this->modx->getOption('virtualpage_fastrouter_key', null, 'fastrouter');

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

	/**
	 * @param array $sp
	 * @return bool|null
	 */
	public function doRoutes($sp = array())
	{
		$ids = $sp['routes'];
		$this->event = $sp['eventName'];
		if(empty($ids)) {
			$this->modx->log(1, print_r('[virtualpage]:Error empty routes for event - ' . $this->event, 1));
			return false;
		}
		//
		$this->routes = $this->generateRouteArray($ids);
		$dispatcher = $this->getDispatcher();
		//
		$uri = $this->getUri();
		$params = $dispatcher->dispatch($this->getMethod(), $uri);
		switch ($params[0]) {
			case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				return $this->error();
				break;
			case FastRoute\Dispatcher::FOUND:
				$params[2]['uri'] = $uri;
				$params[2]['routes'] = $ids;
				return $this->handle($params[1], $params[2]);
				break;
		}

		return true;
	}

	/**
	 * @param $id
	 * @param array $data
	 * @return null
	 */
	public function handle($id, array $data) {
		if(!$handler = $this->modx->getObject('vpHandler', array('id' => $id, 'active' => 1))) {
			return $this->error();
		}
		$_REQUEST += array($this->fastrouterKey => $data);
		// set placeholders
		$routes = $data['routes'];
		unset($data['routes']);
		foreach($routes as $id => $z) {
			if(!$route = $this->modx->getObject('vpRoute', $id)) {continue;}
			$properties = $route->get('properties');
			if(empty($properties) || !is_array($properties)) {continue;}
			$data = array_merge($data, $properties);
		}
		$type = $handler->get('type');
		$entry = $handler->get('entry');
		$content = $handler->get('content');
		$prefix = $this->modx->getOption('virtualpage_prefix_placeholder', null, 'vp.');
		$data['description'] = $handler->get('description');
		$this->modx->setPlaceholders($data, $prefix);
		$output = '';
		switch ($type) {
			case 0:
				$this->modx->sendForward($entry);
				break;
			case 1:
				$output = $this->process('modSnippet', $entry, $data, $content);
				break;
			case 2:
				$output = $this->process('modChunk', $entry, $data, $content);
				break;
			case 3:
				$output = $this->process('modResource', $entry, $data, $content);
				break;
			default:
				$output = $this->error();
				break;
		}
		exit($output);
	}

	/**
	 * @param $ids
	 * @return mixed
	 */
	public function generateRouteArray($ids)
	{
		$key = 'route';
		$cacheKey = $this->config['cache_key']
			. $key
			. '/'
			. $this->event;

		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$routes = $this->modx->getCacheManager()->get($key, $cacheOptions);
		if(!empty($routes)) {return $routes;}
		//
		$match = array();
		foreach ($ids as $n => $v) {
			if(!$route = $this->modx->getObject('vpRoute', array('id' => $n, 'active' => 1))) {continue;}
			foreach ((array) explode(',', $route->get('metod')) as $method) {
				if((!empty($match[$route->get('route')]))
					&& (in_array($method, array_values($match[$route->get('route')])))) {
					continue;
				}
				$routes[] = array(
					$method,
					$route->get('route'),
					$route->get('handler'),
				);
				$match[$route->get('route')][] = $method;
			}
		}
		$this->modx->cacheManager->set($key, $routes, 0, $cacheOptions);

		return $routes;
	}

	/**
	 * @return FastRoute\Dispatcher|FastRoute\Dispatcher\GroupCountBased
	 */
	public function getDispatcher() {
		if (!isset($this->dispatcher[$this->event])) {
			// create fastrouter path
			$key = $this->config['fastrouter_cache_key'];
			$this->createCachePath($key);
			$cache = $this->modx->getOption(xPDO::OPT_CACHE_PATH)
				. $this->config['cache_key']
				. $key;
			//
			$this->dispatcher[$this->event] = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $router) {
				$this->getRoutes($router);
			}, array('cacheFile' => $cache.'/'.$this->event.'.cache.php'));
		}
		return $this->dispatcher[$this->event];
	}

	/**
	 * @param FastRoute\RouteCollector $router
	 */
	protected function getRoutes(FastRoute\RouteCollector $router) {
		$routes = $this->routes;
		if (!$routes) {
			throw new InvalidArgumentException('Invalid routes');
		}
		foreach ($routes as $r) {
			$router->addRoute($r[0], $r[1], $r[2]);
		}
	}

	/**
	 * @return mixed
	 */
	public function getMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * @return string
	 */
	public function getUri() {
		$alias = $this->modx->getOption('request_alias', null, 'q');
		$uri = isset($_REQUEST[$alias]) ? (string) $_REQUEST[$alias] : '';
		return '/' . ltrim($uri, '/');
	}

	/**
	 * @return string
	 */
	public function error() {
		$this->modx->sendErrorPage(array('vp_die' => true));
		return '';
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
	 * create cache path for $key
	 *
	 * @param string $key
	 */
	public function createCachePath($key = 'fastrouter')
	{
		$cacheKey = $this->config['cache_key'].$key;
		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$empty = $this->modx->getCacheManager()->get($key, $cacheOptions);
		//
		if(empty($empty)) {
			$empty['empty'] = 1;
			$this->modx->cacheManager->set($key, $empty, 0, $cacheOptions);
		}
	}

	/**
	 * @param string $object
	 * @param $entry
	 *
	 * @return string
	 */
	public function process($object = 'modSnippet', $entry, $data, $content) {
		$output = '';
		switch ($object) {
			case 'modResource': {
				$res = $this->modx->newObject('modResource');
				$res->set('id', $this->modx->getOption('site_start'));
				$res->fromArray(array(
					'pagetitle' => $data['description'],
					'template' => $entry,
					'content' => $content
				));
				$this->modx->resource = $res;
				$this->modx->getResponse();
				$output = $this->modx->response->outputContent();
				break;
			}
			case 'modChunk':
			case 'modSnippet': {
				if($snippet = $this->modx->getObject($object, $entry)) {
					$snippet->_cacheable = false;
					$snippet->_processed = false;
					$properties = $snippet->getProperties();
					$output = $snippet->process($properties);
					if (strpos($output, '[[') !== false) {
						$maxIterations= intval($this->modx->getOption('parser_max_iterations', null, 10));
						$this->modx->parser->processElementTags('', $output, true, false, '[[', ']]', array(), $maxIterations);
						$this->modx->parser->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);
					}
				}
				break;
			}
			default:
				break;
		}

		return $output;
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