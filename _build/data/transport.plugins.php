<?php

$plugins = array();

$tmp = array(
    /*'vpService' => array(
        'file'        => 'virtualpage_s',
        'description' => '',
        'locked'      => 0,
        'events'      => array(
            'OnHandleRequest'     => array(),
            'OnBeforeCacheUpdate' => array(),
        )
    ),*/
    /*'vpEvent' => array(
        'file'        => 'virtualpage',
        'description' => '',
        'locked'      => 1,
        'events'      => array()
    ),*/

    /*'vpInit'   => array(
        'file'        => 'init',
        'description' => '',
        'events'      => array(
            'OnMODXInit' => array('priority' => 50),
        )
    ),*/
    'vpSystem' => array(
        'file'        => 'system',
        'description' => '',
        'events'      => array(
            'OnBeforeCacheUpdate' => array('priority' => 50),
            'OnHandleRequest'     => array('priority' => 50),
            'OnPageNotFound'      => array('priority' => 50),
        )
    ),


);

foreach ($tmp as $k => $v) {
    /* @avr modplugin $plugin */
    $plugin = $modx->newObject('modPlugin');
    $plugin->fromArray(array(
        'name'        => $k,
        'category'    => 0,
        'description' => @$v['description'],
        'plugincode'  => getSnippetContent($sources['source_core'] . '/elements/plugins/plugin.' . $v['file'] . '.php'),
        'static'      => BUILD_PLUGIN_STATIC,
        'source'      => 1,
        'static_file' => 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/plugin.' . $v['file'] . '.php'
    ), '', true, true);

    $events = array();
    if (!empty($v['events'])) {
        foreach ($v['events'] as $k2 => $v2) {
            /* @var modPluginEvent $event */
            $event = $modx->newObject('modPluginEvent');
            $event->fromArray(array_merge(
                array(
                    'event'       => $k2,
                    'priority'    => 0,
                    'propertyset' => 0,
                ), $v2
            ), '', true, true);
            $events[] = $event;
        }
        unset($v['events']);
    }

    if (!empty($events)) {
        $plugin->addMany($events);
    }

    $plugins[] = $plugin;
}

unset($tmp, $properties);
return $plugins;