<?php

/**
 * The base class for virtualpage.
 */

include_once dirname(dirname(__FILE__)) . '/lib/fastroute/src/bootstrap.php';

class virtualpage
{
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
	function __construct(modX &$modx, array $config = array())
	{
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
	public function getOption($key, $config = array(), $default = null)
	{
		$option = $default;
		if (!empty($key) && is_string($key)) {
			if ($config != null && array_key_exists($key, $config)) {
				$option = $config[$key];
			} elseif (array_key_exists($key, $this->config)) {
				$option = $this->config[$key];
			} elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
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
		/* check site status */
		if (!$this->modx->checkSiteStatus()) {
			return false;
		}
		$ids = $sp['routes'];
		$this->event = $sp['eventName'];
		if (empty($ids)) {
			$this->modx->log(1, print_r('[virtualpage]:Error empty routes for event - ' . $this->event, 1));
			return false;
		}
		$uri = $this->getUri();
		$this->routes = $this->generateRouteArray($ids);
		$dispatcher = $this->getDispatcher();
		$params = $dispatcher->dispatch($this->getMethod(), $uri);
		switch ($params[0]) {
			/*case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				return $this->error();
				break;*/
			case FastRoute\Dispatcher::FOUND:
				$opts = array();
				$opts['cache_key'] = 'event/properties/' . $this->event;
				$properties = $this->getCache($opts);
				foreach ($ids as $id => $z) {
					$found = array();
					$route = $properties[$id]['route'];
					$property = $properties[$id]['properties'];
					if (empty($property) || empty($route)) {
						continue;
					}
					preg_match_all("/{([^}]+)}*/i", $route, $found);
					$url = str_replace($found[0], array_values($params[2]), $route);
					if ($url == $uri) {
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
	public function handle($id, array $data)
	{
		if (!$handler = $this->modx->getObject('vpHandler', array('id' => $id, 'active' => 1))) {
			return $this->error();
		}
		$_REQUEST += array($this->fastrouterKey => $data);
		$type = $handler->get('type');
		$entry = $handler->get('entry');
		$data['description'] = $handler->get('description');
		$data['content'] = $handler->get('content');
		$data['cache'] = $handler->get('cache');
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
		$opts = array();
		$opts['cache_key'] = 'event/route/' . $this->event;
		if ($routes = $this->getCache($opts)) {
			return $routes;
		}
		$match = $properties = array();
		foreach ($ids as $n => $v) {
			if (!$route = $this->modx->getObject('vpRoute', array('id' => $n, 'active' => 1))) {
				continue;
			}
			foreach ((array)explode(',', $route->get('metod')) as $method) {
				if ((!empty($match[$route->get('route')]))
					&& (in_array($method, array_values($match[$route->get('route')])))
				) {
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
		$this->setCache($routes, $opts);
		$opts['cache_key'] = 'event/properties/' . $this->event;
		$this->setCache($properties, $opts);
		return $routes;
	}

	/**
	 * @return FastRoute\Dispatcher|FastRoute\Dispatcher\GroupCountBased
	 */
	public function getDispatcher()
	{
		$cacheKey = $this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/';
		if (isset($this->config['fastrouter_cache_key'])) {
			$cacheKey .= $this->config['fastrouter_cache_key'];
		} else {
			$cacheKey .= $this->modx->getOption('virtualpage_fastrouter_cache_key', null, 'virtualpage/event/');
		}
		if (!isset($this->dispatcher[$this->event])) {
			$this->dispatcher[$this->event] = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $router) {
				$this->getRoutes($router);
			}, array('cacheFile' => $cacheKey . '/' . $this->event . '.cache.php'));
		}
		return $this->dispatcher[$this->event];
	}

	/**
	 * @param FastRoute\RouteCollector $router
	 */
	protected function getRoutes(FastRoute\RouteCollector $router)
	{
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
	public function getMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * @return string
	 */
	public function getUri()
	{
		$alias = $this->modx->getOption('request_alias', null, 'q');
		$uri = isset($_REQUEST[$alias]) ? (string)$_REQUEST[$alias] : '';
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
		if (empty($nameEvent)) {
			return false;
		}
		if ($plugin = $this->modx->getObject('modPlugin', array('name' => $namePlugin))) {
			$this->clearCache(array('cache_key' => 'event/'));
			$id = $plugin->get('id');
			if (($action == 'create') || ($action == 'update')) {
				if (!$event = $this->modx->getObject('modPluginEvent', array('pluginid' => $id, 'event' => $nameEvent))) {
					$event = $this->modx->newObject('modPluginEvent');
				}
				$event->set('pluginid', $id);
				$event->set('event', $nameEvent);
				$event->set('priority', $priority);
				if ($event->save()) {
					//$this->modx->cacheManager->refresh();
					return true;
				}
			} else {
				//remove
				if ($event = $this->modx->getObject('modPluginEvent', array('pluginid' => $id, 'event' => $nameEvent))) {
					if ($event->remove()) {
						//$this->modx->cacheManager->refresh();
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
		$opts = array();
		$opts['cache_key'] = 'event/all';
		$listEvent = $this->getCache($opts);
		if (empty($listEvent) && $this->modx->getCount('vpEvent') > 0) {
			$data['active'] = 1;
			$tmp = $this->runProcessor('mgr/settings/event/getlist', $data);
			if ($response = json_decode($tmp->response, 1)) {
				foreach ($response['results'] as $v) {
					if (empty($v['routes'])) {
						continue;
					}
					$listEvent[$v['name']] = $v['routes'];
				}
			}
			$this->setCache($listEvent, $opts);
		}
		return $listEvent;
	}

	/**
	 * @param string $object
	 * @param $entry
	 *
	 * @return string
	 */
	public function process($object = 'modSnippet', $entry, $data)
	{
		$output = '';
		$description = $data['description'];
		$content = $data['content'];
		$cache = $data['cache'];
		$request = $data['request'];
		unset(
			$data['content'],
			$data['request']
		);
		$this->modx->setPlaceholders($data, $this->modx->getOption('virtualpage_prefix_placeholder', null, 'vp.'));

		switch ($object) {
			case 'sendForward': {
				$this->modx->sendForward($entry);
				break;
			}
			case 'modResource': {
				$this->getResource(array(
						'pagetitle' => $description,
						'template' => $entry,
						'content' => $content,
						'cache' => $cache,
						'request' => $request,
					)
				);
				break;
			}
			case 'modChunk':
			case 'modSnippet': {
				$output = $this->getSnippet(array(
					'object' => $object,
					'entry' => $entry,
					'cache' => $cache,
					'request' => $request,
				));
				break;
			}
			default:
				break;
		}
		return $output;
	}

	/**
	 * @param array $data
	 *
	 * @return mixed|string
	 */
	public function getSnippet(array $data = array())
	{
		if (!isset($data['entry']) OR !isset($data['object'])) {
			return '';
		}
		$output = '';
		$opts = array();
		$opts['cache_key'] = ($data['object'] == 'modSnippet') ? 'snippet' : 'chunk';
		$opts['cache_key'] = implode('/', array($opts['cache_key'], sha1(serialize($opts + $data))));
		if (!empty($data['cache']) AND $cachedSnippet = $this->getCache($opts)) {
			return $cachedSnippet;
		}
		if ($snippet = $this->modx->getObject($data['object'], $data['entry'])) {
			$snippet->_cacheable = false;
			$snippet->_processed = false;
			$properties = $snippet->getProperties();
			$properties = array_merge($properties, $data);
			$output = $snippet->process($properties);
			if (strpos($output, '[[') !== false) {
				$maxIterations = intval($this->modx->getOption('parser_max_iterations', null, 10));
				$this->modx->parser->processElementTags('', $output, true, false, '[[', ']]', array(), $maxIterations);
				$this->modx->parser->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);
			}
			$output = str_replace("[^q^]", '', $output);
			$output = str_replace("[^qt^]", '', $output);
			$output = str_replace("[^p^]", '', $output);
			$output = str_replace("[^t^]", '', $output);
			$output = str_replace("[^s^]", '', $output);
		}
		if (!empty($data['cache'])) {
			$this->setCache($output, $opts);
		}
		return $output;
	}

	/**
	 * @param array $data
	 */
	public function getResource(array $data = array())
	{
		$opts = array();
		$opts['cache_key'] = 'resource';
		$opts['cache_key'] = implode('/', array($opts['cache_key'], sha1(serialize($opts + $data))));

		if (empty($data['cache'])) {
			$res = $this->newResource($data);
			$this->modx->resource = $res;
			$this->modx->getResponse();
			$this->modx->response->outputContent();
		} else {
			$cachedResource = $this->getCache($opts);
			if (is_array($cachedResource) && array_key_exists('resource', $cachedResource) && is_array($cachedResource['resource'])) {
				/** @var modResource $resource */
				$resource = $this->modx->newObject($cachedResource['resourceClass']);
				if ($resource) {
					$resource->fromArray($cachedResource['resource'], '', true, true, true);
					$resource->_content = $cachedResource['resource']['_content'];
					if (isset($cachedResource['elementCache'])) $this->modx->elementCache = $cachedResource['elementCache'];
					if (isset($cachedResource['sourceCache'])) $this->modx->sourceCache = $cachedResource['sourceCache'];
					if ($resource->get('_jscripts')) $this->modx->jscripts = $this->modx->jscripts + $resource->get('_jscripts');
					if ($resource->get('_sjscripts')) $this->modx->sjscripts = $this->modx->sjscripts + $resource->get('_sjscripts');
					if ($resource->get('_loadedjscripts')) $this->modx->loadedjscripts = array_merge($this->modx->loadedjscripts, $resource->get('_loadedjscripts'));
					$resource->setProcessed(true);
				}
				// from cache
				$this->modx->resource = $resource;
				$this->modx->request->prepareResponse();
			} else {
				// create new
				$res = $this->newResource($data);
				$this->modx->resource = $res;
			}
			$this->modx->resource->_output = $this->modx->resource->process();
			$this->modx->resource->_jscripts = $this->modx->jscripts;
			$this->modx->resource->_sjscripts = $this->modx->sjscripts;
			$this->modx->resource->_loadedjscripts = $this->modx->loadedjscripts;
			/* collect any uncached element tags in the content and process them */
			$this->modx->getParser();
			$maxIterations = intval($this->modx->getOption('parser_max_iterations', null, 10));
			$this->modx->parser->processElementTags('', $this->modx->resource->_output, true, false, '[[', ']]', array(), $maxIterations);
			$this->modx->parser->processElementTags('', $this->modx->resource->_output, true, true, '[[', ']]', array(), $maxIterations);
			//
			if (($js = $this->modx->getRegisteredClientStartupScripts()) && (strpos($this->modx->resource->_output, '</head>') !== false)) {
				/* change to just before closing </head> */
				$this->modx->resource->_output = preg_replace("/(<\/head>)/i", $js . "\n\\1", $this->modx->resource->_output, 1);
			}
			/* Insert jscripts & html block into template - template must have a </body> tag */
			if ((strpos($this->modx->resource->_output, '</body>') !== false) && ($js = $this->modx->getRegisteredClientScripts())) {
				$this->modx->resource->_output = preg_replace("/(<\/body>)/i", $js . "\n\\1", $this->modx->resource->_output, 1);
			}
			$totalTime = (microtime(true) - $this->modx->startTime);
			$queryTime = $this->modx->queryTime;
			$queryTime = sprintf("%2.4f s", $queryTime);
			$queries = isset ($this->modx->executedQueries) ? $this->modx->executedQueries : 0;
			$totalTime = sprintf("%2.4f s", $totalTime);
			$phpTime = $totalTime - $queryTime;
			$phpTime = sprintf("%2.4f s", $phpTime);
			$source = $this->modx->resourceGenerated ? "database" : "cache";
			$this->modx->resource->_output = str_replace("[^q^]", $queries, $this->modx->resource->_output);
			$this->modx->resource->_output = str_replace("[^qt^]", $queryTime, $this->modx->resource->_output);
			$this->modx->resource->_output = str_replace("[^p^]", $phpTime, $this->modx->resource->_output);
			$this->modx->resource->_output = str_replace("[^t^]", $totalTime, $this->modx->resource->_output);
			$this->modx->resource->_output = str_replace("[^s^]", $source, $this->modx->resource->_output);
			// to cache
			$obj = $this->modx->resource;
			$results = array();
			$results['resourceClass'] = $obj->_class;
			$results['resource']['_processed'] = $obj->getProcessed();
			$results['resource'] = $obj->toArray('', true);
			$results['resource']['_content'] = $obj->_content;
			if ($contentType = $obj->getOne('ContentType')) {
				$results['contentType'] = $contentType->toArray('', true);
			}
			if (!empty($this->modx->elementCache)) {
				$results['elementCache'] = $this->modx->elementCache;
			}
			if (!empty($this->modx->sourceCache)) {
				$results['sourceCache'] = $this->modx->sourceCache;
			}
			if (!empty($obj->_sjscripts)) {
				$results['resource']['_sjscripts'] = $obj->_sjscripts;
			}
			if (!empty($obj->_jscripts)) {
				$results['resource']['_jscripts'] = $obj->_jscripts;
			}
			if (!empty($obj->_loadedjscripts)) {
				$results['resource']['_loadedjscripts'] = $obj->_loadedjscripts;
			}
		}
		if (!empty($results)) {
			$this->setCache($results, $opts);
		}
		$output = $this->modx->resource->_output;
		exit($output);
	}

	/**
	 * @param array $data
	 * @return null|object
	 */
	public function newResource(array $data = array())
	{
		$resource = $this->modx->newObject('modResource');
		$resource->fromArray($data);
		if (!isset($data['id'])) {
			$resource->set('id', $this->modx->getOption('site_start'));
		}
		$this->modx->invokeEvent('vpOnResourceAfterCreate', array(
			'mode' => modSystemEvent::MODE_NEW,
			'resource' => &$resource,
		));
		return $resource;
	}

	/**
	 * Sets data to cache
	 *
	 * @param mixed $data
	 * @param mixed $options
	 *
	 * @return string $cacheKey
	 */
	public function setCache($data = array(), $options = array())
	{
		$cacheKey = $this->getCacheKey($options);
		$cacheOptions = $this->getCacheOptions($options);
		if (!empty($cacheKey) && !empty($cacheOptions) && $this->modx->getCacheManager()) {
			$this->modx->cacheManager->set(
				$cacheKey,
				$data,
				$cacheOptions[xPDO::OPT_CACHE_EXPIRES],
				$cacheOptions
			);
		}
		return $cacheKey;
	}

	/**
	 * Returns data from cache
	 *
	 * @param mixed $options
	 *
	 * @return mixed
	 */
	public function getCache($options = array())
	{
		$cacheKey = $this->getCacheKey($options);
		$cacheOptions = $this->getCacheOptions($options);
		$cached = '';
		if (!empty($cacheOptions) && !empty($cacheKey) && $this->modx->getCacheManager()) {
			$cached = $this->modx->cacheManager->get($cacheKey, $cacheOptions);
		}
		return $cached;
	}

	public function clearCache($options = array())
	{
		$cacheKey = $this->getCacheKey($options);
		$cacheOptions = $this->getCacheOptions($options);
		$cacheOptions['cache_key'] .= $cacheKey;
		if (!empty($cacheOptions) && $this->modx->getCacheManager()) {
			return $this->modx->cacheManager->clean($cacheOptions);
		}
		return false;
	}

	/**
	 * Returns array with options for cache
	 *
	 * @param $options
	 *
	 * @return array
	 */
	protected function getCacheOptions($options = array())
	{
		if (empty($options)) {
			$options = $this->config;
		}
		$cacheOptions = array(
			xPDO::OPT_CACHE_KEY => empty($options['cache_key'])
				? 'default'
				: 'default/' . $this->namespace . '/',
			xPDO::OPT_CACHE_HANDLER => !empty($options['cache_handler'])
				? $options['cache_handler']
				: $this->modx->getOption('cache_resource_handler', null, 'xPDOFileCache'),
			xPDO::OPT_CACHE_EXPIRES => $options['cacheTime'] !== ''
				? (integer)$options['cacheTime']
				: (integer)$this->modx->getOption('cache_resource_expires', null, 0),
		);
		return $cacheOptions;
	}

	/**
	 * Returns key for cache of specified options
	 *
	 * @var mixed $options
	 *
	 * @return bool|string
	 */
	protected function getCacheKey($options = array())
	{
		if (empty($options)) {
			$options = $this->config;
		}
		if (!empty($options['cache_key'])) {
			return $options['cache_key'];
		}
		$key = !empty($this->modx->resource)
			? $this->modx->resource->getCacheKey()
			: '';
		return $key . '/' . sha1(serialize($options));
	}

	/**
	 * Shorthand for load and run an processor in this component
	 *
	 * @param string $action
	 * @param array $scriptProperties
	 *
	 * @return mixed
	 */
	function runProcessor($action = '', $scriptProperties = array())
	{
		$this->modx->error->errors = $this->modx->error->message = null;
		return $this->modx->runProcessor($action, $scriptProperties, array(
				'processors_path' => $this->config['processorsPath']
			)
		);
	}

	/**
	 * @return string
	 */
	public function error()
	{
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

	/**
	 * @param $sp
	 */
	public function OnBeforeCacheUpdate($sp)
	{
		// clear cache
		//$this->clearCache();
	}

}