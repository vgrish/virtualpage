<?php

$files = scandir(dirname(__FILE__));
foreach ($files as $file) {
	@include_once($file);
}

$_lang['virtualpage'] = 'virtualpage';
$_lang['vp_menu_desc'] = 'Пример расширения для разработки.';

$_lang['vp_settings'] = 'Настройки';

$_lang['vp_route'] = 'Маршруты';
$_lang['vp_route_intro'] = 'Панель управления маршрутами.';

$_lang['vp_event'] = 'События';
$_lang['vp_event_intro'] = 'Панель управления событиями.';
