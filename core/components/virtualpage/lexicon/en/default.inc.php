<?php

$files = scandir(dirname(__FILE__));
foreach ($files as $file) {
	@include_once($file);
}

$_lang['virtualpage'] = 'virtualpage';
$_lang['vp_menu_desc'] = 'Managing virtual pages.';

$_lang['vp_settings'] = 'Settings';

$_lang['vp_routes'] = 'Routes';
$_lang['vp_routes_intro'] = 'Control panel routes.';

$_lang['vp_events'] = 'Events';
$_lang['vp_events_intro'] = 'Control panel events.';

$_lang['vp_handlers'] = 'Handlers';
$_lang['vp_handlers_intro'] = 'Control panel handlers.';
