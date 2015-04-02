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
		$uri = $this->getUri();
		$this->routes = $this->generateRouteArray($ids);
		$dispatcher = $this->getDispatcher();
		$params = $dispatcher->dispatch($this->getMethod(), $uri);
		switch ($params[0]) {
			/*case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				return $this->error();
				break;*/
			case FastRoute\Dispatcher::FOUND:
				$properties = $this->getCache('properties');
				foreach($ids as $id => $z) {
					$found = array();
					$route = $properties[$id]['route'];
					$property = $properties[$id]['properties'];
					if(empty($property) || empty($route)) {continue;}
					preg_match_all("/{([^}]+)}*/i", $route, $found);
					$url = str_replace($found[0], array_values($params[2]), $route);
					if($url == $uri) {
						$params[2] = array_merge($params[2], $property);
						break;
					}
				}
				$params[2]['uri'] = $uri;
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
		$type = $handler->get('type');
		$entry = $handler->get('entry');
		$data['description'] = $handler->get('description');
		$data['content'] = $handler->get('content');
		$data['request'] = $_REQUEST;
		$output = '';
		switch ($type) {
			case 0:
				$output = $this->process('sendForward', $entry, $data);
				break;
			case 1:
				$output = $this->process('modSnippet', $entry, $data);
				break;
			case 2:
				$output = $this->process('modChunk', $entry, $data);
				break;
			case 3:
				$output = $this->process('modResource', $entry, $data);
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
		$key = 'route.'.$this->event;
		$routes = $this->getCache($key);
		if(!empty($routes)) {return $routes;}
		$match = $properties = array();
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
				$properties[$route->get('id')] = array(
					'route' => $route->get('route'),
					'properties' => $route->get('properties'),
				);
				$match[$route->get('route')][] = $method;
			}
		}
		$this->setCache($key, $routes);
		$key = 'properties';
		$this->setCache($key, $properties);

		return $routes;
	}

	/**
	 * @return FastRoute\Dispatcher|FastRoute\Dispatcher\GroupCountBased
	 */
	public function getDispatcher() {
		if (!isset($this->dispatcher[$this->event])) {
			$key = $this->config['fastrouter_cache_key'];
			$cache = $this->modx->getOption(xPDO::OPT_CACHE_PATH) . $this->config['cache_key'] . $key;
			$this->dispatcher[$this->event] = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $router) {
				$this->getRoutes($router);
			}, array('cacheFile' => $cache.'.'.$this->event.'.cache.php'));
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
		$ListEvent = $this->getCache($key);
		if (empty($ListEvent) && $this->modx->getCount('vpEvent') > 0) {
			$data['active'] = 1;
			$tmp = $this->runProcessor('mgr/settings/event/getlist', $data);
			if ($response = json_decode($tmp->response, 1)) {
				foreach ($response['results'] as $v) {
					if(empty($v['routes'])) {continue;}
					$ListEvent[$v['name']] = $v['routes'];
				}
			}
			$this->setCache($key, $ListEvent);
		}

		return $ListEvent;
	}

	/**
	 * @param string $object
	 * @param $entry
	 *
	 * @return string
	 */
	public function process($object = 'modSnippet', $entry, $data) {
		$output = '';
		$description = $data['description'];
		$content = $data['content'];
		$request = $data['request'];
		unset($data['content'], $data['request']);
		$prefix = $this->modx->getOption('virtualpage_prefix_placeholder', null, 'vp.');
		$this->modx->setPlaceholders($data, $prefix);
		//
		switch ($object) {
			case 'sendForward': {
				$this->modx->sendForward($entry);
				break;
			}
			case 'modResource': {
				$res = $this->modx->newObject('modResource');
				$res->set('id', $this->modx->getOption('site_start'));
				$res->fromArray(array(
					'pagetitle' => $description,
					'template' => $entry,
					'content' => $content
				));
				$this->modx->resource = $res;
				$this->modx->getResponse();
				$this->modx->response->outputContent();
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
	 * @param $key
	 * @param array $data
	 * @return mixed
	 */
	public function setCache($key, $data = array())
	{
		if(empty($key)) {return $key;}
		$cacheKey = $this->config['cache_key'];
		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$this->modx->cacheManager->set($key, $data, 0, $cacheOptions);

		return $key;
	}

	/**
	 * @param $key
	 * @return mixed|string
	 */
	public function getCache($key)
	{
		$cached = '';
		if(empty($key)) {return $cached;}
		$cacheKey = $this->config['cache_key'];
		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$cached = $this->modx->getCacheManager()->get($key, $cacheOptions);

		return $cached;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function clearCache($key = 'event')
	{
		if(empty($key)) {return $key;}
		$cacheKey = $this->config['cache_key'];
		$cacheOptions = array(xPDO::OPT_CACHE_KEY => $cacheKey);
		$this->modx->cacheManager->clean($cacheOptions);

		return $key;
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
	 * @return string
	 */
	public function error() {
		$this->modx->sendErrorPage(array('vp_die' => true));
		return '';
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