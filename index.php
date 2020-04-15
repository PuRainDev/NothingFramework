<?php
/*
 * This file is part of NothingFramework.
 *
 * (c) PuRain
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
use PuRain\Nothing_Framework as Nothing;

require_once 'core/Framework.php';

$modules = Nothing::get_modules();
$project_settings = Nothing::get_project_settings();
$params = Nothing::get_params();
$aliases = parse_ini_file("aliases.ini", true);

define('ENVIRONMENT', $project_settings['information']['sys_status']);

if (defined('ENVIRONMENT')) {
	switch (ENVIRONMENT) {
		case 'development':
			error_reporting(E_ALL);
			break;
		case 'testing':
		case 'production':
			error_reporting(0);
			break;
		default:
			exit('The application environment is not set correctly.');
	}
}

/**
 * Checks called module and transfers data to it.
 */
if(isset($params[0]) && $params['0']!='Nothing'){
	$module_name = Nothing::alias($params[0]);
	$params = array_slice($params, 1);
	if(Nothing::check_module($module_name)) {
		$module_settings = Nothing::get_module_settings($module_name);
		if ($module_settings['properties']['is_accessible'] == "true") {
			echo Nothing::call_module($module_name, $params);
		} else {
			Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array('4', 'SYS_Error_Code_403', 'Forbidden. You don\'t have permission to access this location', 'index'));
		}
	}
} else {
	echo Nothing::call_module($project_settings['general']['default_module'], array('Nothing'));
}
?>