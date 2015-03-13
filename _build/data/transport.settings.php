<?php

$settings = array();

$tmp = array(

	'active' => array( // включить подгрузку плагинов
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'virtualpage_main',
	),

	'exclude_event_groupname' => array(
		'xtype' => 'textarea',
		'value' => 'Categories,Chunks,Contexts,Internationalization,Media Sources,Plugin Events,Plugins,Property Sets,Resources,RichText Editor,Security,Snippets,Template Variables,Templates,User Groups,Users',
		'area' => 'virtualpage_part_1',
	),
	// exclude_event_groupname
	//временные

	'assets_path' => array(
		'xtype' => 'textfield',
		'value' => '{base_path}virtualpage/assets/components/virtualpage/',
		'area' => 'virtualpage_temp',
	),
	'assets_url' => array(
		'xtype' => 'textfield',
		'value' => '/virtualpage/assets/components/virtualpage/',
		'area' => 'virtualpage_temp',
	),
	'core_path' => array(
		'xtype' => 'textfield',
		'value' => '{base_path}virtualpage/core/components/virtualpage/',
		'area' => 'virtualpage_temp',
	),

	/*
	'some_setting' => array(
		'xtype' => 'combo-boolean',
		'value' => true,
		'area' => 'virtualpage_main',
	),
	*/
);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'virtualpage_' . $k,
			'namespace' => PKG_NAME_LOWER,
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
