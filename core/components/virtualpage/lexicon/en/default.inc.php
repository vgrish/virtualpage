<?php

$files = scandir(dirname(__FILE__));
foreach ($files as $file) {
	@include_once($file);
}

$_lang['virtualpage'] = 'virtualpage';
$_lang['virtualpage_menu_desc'] = 'A sample Extra to develop from.';
$_lang['virtualpage_intro_msg'] = 'You can select multiple items by holding Shift or Ctrl button.';

