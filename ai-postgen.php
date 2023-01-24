<?php
/*
Plugin Name: AI Postgen
Plugin URI: http://www.janmichalicka.com
Description: AI Postgen
Author: Jan Michalicka
Author URI: http://www.janmichalicka.com
Version: 1.0
*/

define('AIPG_PLUGIN_DIR', str_replace('\\','/',dirname(__FILE__)));
require_once(AIPG_PLUGIN_DIR.'/ai-options.php');
require_once(AIPG_PLUGIN_DIR.'/ai-moderate.php');
require_once(AIPG_PLUGIN_DIR.'/ai-public.php');

function aipg_menu() {
  add_options_page('AI Postgen Options', 'AI Postgen', 8, __FILE__, 'aipg_options');
  add_management_page('AI Postgen Moderate', 'AI Moderate', 8, __FILE__, 'aipg_moderate');
}

add_action('admin_menu', 'aipg_menu');

