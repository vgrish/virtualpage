<?php

$files = scandir(dirname(__FILE__));
foreach ($files as $file) {
	@include_once($file);
}

$_lang['virtualpage'] = 'VirtualPage';
$_lang['vp_menu_desc'] = 'Управление виртуальными страницами.';

$_lang['vp_settings'] = 'Настройки';

$_lang['vp_routes'] = 'Маршруты';
$_lang['vp_routes_intro'] = 'Панель управления маршрутами.';

$_lang['vp_events'] = 'События';
$_lang['vp_events_intro'] = 'Панель управления событиями.';

$_lang['vp_handlers'] = 'Обработчики';
$_lang['vp_handlers_intro'] = 'Панель управления обработчиками.';
