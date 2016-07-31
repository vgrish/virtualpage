<?php

/**
 * The base class for virtualpage.
 */

include_once dirname(dirname(dirname(__FILE__))) . '/vendor/fastroute/src/bootstrap.php';

class virtualpage
{
    /* @var modX $modx */
    public $modx;

    public $namespace = 'virtualpage';
    public $version = '2.0.0-beta';
    public $initialized = array();
    public $config = array();
    public $active = false;

    /**
     * @param modX  $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $this->namespace = $this->getOption('namespace', $config, 'virtualpage');
        $corePath = $this->modx->getOption('virtualpage_core_path', $config,
            $this->modx->getOption('core_path') . 'components/virtualpage/');
        $assetsUrl = $this->modx->getOption('virtualpage_assets_url', $config,
            $this->modx->getOption('assets_url') . 'components/virtualpage/');
        $connectorUrl = $assetsUrl . 'connector.php';

        $this->config = array_merge(array(
            'assetsUrl'      => $assetsUrl,
            'cssUrl'         => $assetsUrl . 'css/',
            'jsUrl'          => $assetsUrl . 'js/',
            'connectorUrl'   => $connectorUrl,
            'corePath'       => $corePath,
            'modelPath'      => $corePath . 'model/',
            'chunksPath'     => $corePath . 'elements/chunks/',
            'templatesPath'  => $corePath . 'elements/templates/',
            'snippetsPath'   => $corePath . 'elements/snippets/',
            'processorsPath' => $corePath . 'processors/',
        ), $config);

        $this->modx->addPackage('virtualpage', $this->config['modelPath']);
        $this->modx->lexicon->load('virtualpage:default');

        $this->active = $this->modx->getOption('virtualpage_active', $config, false);
    }

    /**
     * @param       $n
     * @param array $p
     */
    public function __call($n, array$p)
    {
        echo __METHOD__ . ' says: ' . $n;
    }

    /**
     * @param       $key
     * @param array $config
     * @param null  $default
     *
     * @return mixed|null
     */
    public function getOption($key, $config = array(), $default = null, $skipEmpty = false)
    {
        $option = $default;
        if (!empty($key) AND is_string($key)) {
            if ($config != null AND array_key_exists($key, $config)) {
                $option = $config[$key];
            } elseif (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
            } elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}_{$key}");
            }
        }
        if ($skipEmpty AND empty($option)) {
            $option = $default;
        }

        return $option;
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
            xPDO::OPT_CACHE_KEY     => empty($options['cache_key'])
                ? 'default'
                : 'default/' . $this->namespace . '/',
            xPDO::OPT_CACHE_HANDLER => !empty($options['cache_handler'])
                ? $options['cache_handler']
                : $this->modx->getOption('cache_resource_handler', null, 'xPDOFileCache'),
            xPDO::OPT_CACHE_EXPIRES => isset($options['cacheTime']) AND $options['cacheTime'] !== ''
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
     * @param        $array
     * @param string $delimiter
     *
     * @return array
     */
    public function explodeAndClean($array, $delimiter = ',')
    {
        $array = explode($delimiter, $array);     // Explode fields to array
        $array = array_map('trim', $array);       // Trim array's values
        $array = array_keys(array_flip($array));  // Remove duplicate fields
        $array = array_filter($array);            // Remove empty values from array
        return $array;
    }

    /**
     * @param        $array
     * @param string $delimiter
     *
     * @return array|string
     */
    public function cleanAndImplode($array, $delimiter = ',')
    {
        $array = array_map('trim', $array);       // Trim array's values
        $array = array_keys(array_flip($array));  // Remove duplicate fields
        $array = array_filter($array);            // Remove empty values from array
        $array = implode($delimiter, $array);

        return $array;
    }


    public function getAllRoutes()
    {
        $tmp = array(
            'cache_key' => 'all/routes',
            'cacheTime' => 0,
        );
        if (!$routes = $this->getCache($tmp)) {
            $routes = array();

            $q = $this->modx->newQuery('vpRoute');
            $q->innerJoin('vpEvent', 'vpEvent', "vpEvent.id = vpRoute.event");
            $q->innerJoin('vpHandler', 'vpHandler', "vpHandler.id = vpRoute.handler");

            $q->where(array(
                "vpRoute.active"   => 1,
                "vpEvent.active"   => 1,
                "vpHandler.active" => 1,
            ));
            $q->sortby("vpRoute.rank", "ASC");
            $q->select('vpRoute.metod, vpRoute.route, vpRoute.handler, vpRoute.properties, vpEvent.name as event');
            $q->select($this->modx->getSelectColumns('vpHandler', 'vpHandler', 'handler_'));

            if ($q->prepare() AND $q->stmt->execute()) {
                while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (isset($row['properties'])) {
                        $row['properties'] = json_decode($row['properties'], true);
                    }
                    $routes[] = $row;
                }
            }
            $this->setCache($routes, $tmp);
        }

        return (array)$routes;
    }

    public function getVirtualPageRoutes()
    {
        $tmp = array(
            'cache_key' => 'virtualpage/routes',
            'cacheTime' => 0,
        );
        if (!$routes = $this->getCache($tmp)) {
            $routes = array();
            foreach ($this->getAllRoutes() as $row) {
                $routes[$row['event']][] = $row;
            }
            $this->setCache($routes, $tmp);
        }

        return $routes;
    }

    public function getFastRouteRoutes()
    {
        $tmp = array(
            'cache_key' => 'fastroute/routes',
            'cacheTime' => 0,
        );
        if (!$routes = $this->getCache($tmp)) {
            $routes = array();

            foreach ($this->getAllRoutes() as $row) {
                $metods = $this->getOption('metod', $row);
                $metods = $this->explodeAndClean($metods);

                foreach ($metods as $metod) {
                    $routes[$row['event']][] = array(
                        $metod,
                        $row['route'],
                        $row['handler'],
                    );
                }
            }
            $this->setCache($routes, $tmp);
        }

        return $routes;
    }

    public function clearAllCache()
    {
        $tmp = array('cache_key' => 'default/' . $this->namespace);
        if ($this->modx->getCacheManager()) {
            $this->modx->cacheManager->clean($tmp);
        }
        $this->getVirtualPageRoutes();
        $this->getFastRouteRoutes();
    }


    protected function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    protected function getUri()
    {
        $alias = $this->modx->getOption('request_alias', null, 'q');
        $uri = isset($_REQUEST[$alias]) ? (string)$_REQUEST[$alias] : '';

        return '/' . ltrim($uri, '/');
    }

    protected function getDispatcher($routes = array())
    {
        $cacheKey = $this->modx->getOption(xPDO::OPT_CACHE_PATH) . 'default/' . $this->namespace . '/fastroute/' . sha1(serialize($routes));
        $dispatcher = FastRoute\cachedDispatcher(function (FastRoute\RouteCollector $route) use ($routes) {
            $this->getRoutes($route, $routes);
        }, array('cacheFile' => $cacheKey . '.cache.php'));

        return $dispatcher;
    }


    protected function getRoutes(FastRoute\RouteCollector $route, array $routes = array())
    {
        if (!$routes) {
            throw new InvalidArgumentException('Invalid routes');
        }
        foreach ($routes as $v) {
            $route->addRoute($v[0], $v[1], $v[2]);
        }
    }


    public function dispatch($event = '')
    {
        $virtualPageRoutes = $this->getOption($event, $this->getVirtualPageRoutes());
        $fastRouteRoutes = $this->getOption($event, $this->getFastRouteRoutes());
        if (!$virtualPageRoutes OR !$fastRouteRoutes) {
            return false;
        }

        $uri = $this->getUri();
        $dispatcher = $this->getDispatcher($fastRouteRoutes);
        @list($found, $handlerId, $fastroute) = $dispatcher->dispatch($this->getMethod(), $uri);

        if ($found == FastRoute\Dispatcher::FOUND) {
            foreach ($virtualPageRoutes as $data) {
                $match = array();
                preg_match_all("/{([^}]+)}*/i", $data['route'], $match);
                $url = str_replace($match[0], array_values($fastroute), $data['route']);
                if ($url == $uri) {
                    $data['uri'] = $uri;
                    $data['fastroute'] = $fastroute;

                    return $this->handle($data);
                }
            }
        }

        return true;
    }


    /**
     * @param       $id
     * @param array $data
     *
     * @return null
     */
    public function handle($data = array())
    {
        $this->modx->invokeEvent('vpOnBeforeProcess', array(
            'mode' => modSystemEvent::MODE_NEW,
            'data' => $data,
        ));

        $type = $this->getOption('handler_type', $data);
        $entry = $this->getOption('handler_entry', $data);
        $cache = (boolean)$this->getOption('handler_cache', $data);

        switch ($type) {
            case 0:
                $output = $this->process('sendForward', $entry, $data, $cache);
                break;
            case 1:
                $output = $this->process('modSnippet', $entry, $data, $cache);
                break;
            case 2:
                $output = $this->process('modChunk', $entry, $data, $cache);
                break;
            case 3:
                $output = $this->process('modResource', $entry, $data, $cache);
                break;
            default:
                $output = $this->error();
                break;
        }

        return $output;
    }


    public function process($object = 'modSnippet', $entry = 0, $data, $cache = false)
    {
        $prefix = $this->getOption('prefix_placeholder', null, 'vp.', true);
        $this->modx->setPlaceholders($data, $prefix);

        $key = $this->getOption('fastrouter_key', null, 'fastrouter', true);
        $_REQUEST += array($key => $data);

        $output = '';
        switch ($object) {
            case 'sendForward': {
                $this->modx->sendForward($entry);
                break;
            }
            case 'modResource': {
                $output = $this->getResource($entry, $data, $cache);
                break;
            }
            case 'modChunk':
                $output = $this->getChunk($entry, $data, $cache);
                break;
            case 'modSnippet': {
                $output = $this->getSnippet($entry, $data, $cache);
                break;
            }
            default:
                break;
        }

        return $output;
    }


    public function getChunk($entry = 0, array $data = array(), $cache = false)
    {

        $tmp = array(
            'cache_key' => 'chunk/' . $entry . sha1(serialize($data)),
            'cacheTime' => 0,
        );

        $output = '';
        if (!$cache OR !$output = $this->getCache($tmp)) {
            if ($chunk = $this->modx->getObject('modChunk', $entry)) {
                $chunk->_cacheable = $cache;
                $chunk->_processed = false;
                $properties = $chunk->getProperties();
                $properties = array_merge($properties, $data);
                $output = $chunk->process($properties);
                $output = $this->processTags($output);
            }
        }

        if ($cache) {
            $this->setCache($output, $tmp);
        }

        return $output;
    }

    public function getSnippet($entry = 0, array $data = array(), $cache = false)
    {

        $tmp = array(
            'cache_key' => 'snippet/' . $entry . sha1(serialize($data)),
            'cacheTime' => 0,
        );

        $output = '';
        if (!$cache OR !$output = $this->getCache($tmp)) {
            if ($snippet = $this->modx->getObject('modSnippet', $entry)) {
                $snippet->_cacheable = $cache;
                $snippet->_processed = false;
                $properties = $snippet->getProperties();
                $properties = array_merge($properties, $data);
                $output = $snippet->process($properties);
                $output = $this->processTags($output);
            }
        }

        if ($cache) {
            $this->setCache($output, $tmp);
        }

        return $output;
    }

    public function processTags($output = '')
    {
        if (strpos($output, '[[') !== false) {
            $maxIterations = intval($this->modx->getOption('parser_max_iterations', null, 10));
            $this->modx->parser->processElementTags('', $output, true, false, '[[', ']]', array(), $maxIterations);
            $this->modx->parser->processElementTags('', $output, true, true, '[[', ']]', array(), $maxIterations);
        }

        $totalTime = (microtime(true) - $this->modx->startTime);
        $queryTime = $this->modx->queryTime;
        $queryTime = sprintf("%2.4f s", $queryTime);
        $queries = isset ($this->modx->executedQueries) ? $this->modx->executedQueries : 0;
        $totalTime = sprintf("%2.4f s", $totalTime);
        $phpTime = $totalTime - $queryTime;
        $phpTime = sprintf("%2.4f s", $phpTime);
        $source = $this->modx->resourceGenerated ? "database" : "cache";

        $output = str_replace("[^q^]", $queries, $output);
        $output = str_replace("[^qt^]", $queryTime, $output);
        $output = str_replace("[^p^]", $phpTime, $output);
        $output = str_replace("[^t^]", $totalTime, $output);
        $output = str_replace("[^s^]", $source, $output);

        return $output;
    }

    /**
     * @param array $data
     */
    public function getResource($entry = 0, array $data = array(), $cache = false)
    {
        $tmp = array(
            'cache_key' => 'resource/' . $entry . sha1(serialize($data)),
            'cacheTime' => 0,
        );

        $output = '';
        if (!$cache OR !$results = $this->getCache($tmp)) {

            $this->modx->resource = $this->newResource($data);
            $this->modx->resource->_output = $this->modx->resource->process();
            $this->modx->resource->_jscripts = $this->modx->jscripts;
            $this->modx->resource->_sjscripts = $this->modx->sjscripts;
            $this->modx->resource->_loadedjscripts = $this->modx->loadedjscripts;

            $this->modx->resource->_output = $output = $this->processTags($this->modx->resource->_output);
            if (($js = $this->modx->getRegisteredClientStartupScripts()) && (strpos($this->modx->resource->_output,
                        '</head>') !== false)
            ) {
                /* change to just before closing </head> */
                $this->modx->resource->_output = preg_replace("/(<\/head>)/i", $js . "\n\\1",
                    $this->modx->resource->_output, 1);
            }
            /* Insert jscripts & html block into template - template must have a </body> tag */
            if ((strpos($this->modx->resource->_output,
                        '</body>') !== false) && ($js = $this->modx->getRegisteredClientScripts())
            ) {
                $this->modx->resource->_output = preg_replace("/(<\/body>)/i", $js . "\n\\1",
                    $this->modx->resource->_output, 1);
            }

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

            if ($cache) {
                $this->setCache($results, $tmp);
            }
        }

        if (array_key_exists('resource', $results) AND is_array($results['resource'])) {
            /** @var modResource $resource */
            $resource = $this->modx->newObject($results['resourceClass']);
            if ($resource) {
                $resource->fromArray($results['resource'], '', true, true, true);
                $resource->_content = $results['resource']['_content'];
                if (isset($results['elementCache'])) {
                    $this->modx->elementCache = $results['elementCache'];
                }
                if (isset($results['sourceCache'])) {
                    $this->modx->sourceCache = $results['sourceCache'];
                }
                if ($resource->get('_jscripts')) {
                    $this->modx->jscripts = $this->modx->jscripts + $resource->get('_jscripts');
                }
                if ($resource->get('_sjscripts')) {
                    $this->modx->sjscripts = $this->modx->sjscripts + $resource->get('_sjscripts');
                }
                if ($resource->get('_loadedjscripts')) {
                    $this->modx->loadedjscripts = array_merge($this->modx->loadedjscripts,
                        $resource->get('_loadedjscripts'));
                }
                $resource->setProcessed(true);
            }

            $this->modx->resource = $resource;
            $this->modx->request->prepareResponse();
            $output = $this->modx->resource->_output;
        }

        return $output;
    }

    /**
     * @param array $data
     *
     * @return null|object
     */
    public function newResource(array $data = array())
    {
        /** @var modResource $resource */
        if ($resource = $this->modx->newObject('modResource')) {
            $resource->fromArray(array(
                'pagetitle' => $data['handler_description'] ?: $data['handler_name'],
                'content'   => $data['handler_content'],
                'template'  => $data['handler_entry'],
                'cacheable' => $data['handler_cache'],
                'uri'       => $data['uri'],
                'published' => true,
                'id'        => $this->modx->getOption('site_start')
            ), '', true, true, true);
        }

        $this->modx->invokeEvent('vpOnResourceAfterCreate', array(
            'mode'     => modSystemEvent::MODE_NEW,
            'resource' => &$resource,
            'data'     => $data
        ));

        return $resource;
    }

    public function error()
    {
        $this->modx->sendErrorPage(array('virtualpage_die' => true));

        return true;
    }


}