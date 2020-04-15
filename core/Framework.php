<?php
/*
 * This file is part of NothingFramework.
 *
 * (c) PuRain
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PuRain;

/*
 * The main core of this framework.
 * It checks modules, gets data from module and project setting
 * and parse entered via URL data.
 */
class Nothing_Framework {
	public static function get_modules() {
		$modules = array();
		$skip = array('.', '..');
		$files = scandir('modules');
		foreach($files as $file) {
			if(!in_array($file, $skip))
				array_push($modules, $file);
		}
		return $modules;
		}

	public static function call_module($module, $params) {
		//echo debug_backtrace()[1]['class']." called $module (" . $params['0'] . ") ->";
		if (self::check_module($module)){
			include_once 'modules/'.$module.'/core.php';
			return $module::MainActivity($params);
		} else {
			echo self::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array('0', 'SYS_Error_Code_404', "The page you are looking for doesn't exist", static::class, false));
		}	
	}
	
	public static function check_module($module) {
		if (in_array($module, $GLOBALS['modules'])) {
			if (file_exists('modules/'.$module.'/module_props.json')) {
				return true;
			} else {
				self::call_module("system", array("log", "ERROR", "Called module module_props doesn't exist: $modul damaged."));
				echo self::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "SYS_Error_Code_400", "Bad request, called module doesn't exist", static::class, false));
			}
		} else {
			self::call_module('system', array('log', 'ERROR', 'Called module doesn\'t exist: '.$module));
			echo self::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array('0', 'SYS_Error_Code_400', 'Bad request, called module doesn\'t exist', static::class, false));
		}
	}
	
	public static function get_params() {
		return array_slice(explode('/', ltrim($_SERVER['PATH_INFO'])), 1);
	}
	
	public static function get_project_settings() {
		return json_decode(file_get_contents('config.json'), true);
	}
	
	public static function get_module_settings($module) {
		return json_decode(file_get_contents('modules/'.$module.'/module_props.json'), true);
	}
	
	/*
	* Checks count of params, which were passed to module
	*
	* Usage: Nothing::check_params($params, $min_count)
	*
	* Example: if (Nothing::check_params($params, 3)) {
	*/
	public static function check_params($params, $count) {
		if (count($params) >= $count) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function alias($alias) {
		if (isset($GLOBALS['aliases'][$GLOBALS['project_settings']['information']['sys_status']][$alias]) && $GLOBALS['aliases'][$GLOBALS['project_settings']['information']['sys_status']][$alias] != '') {
			return $GLOBALS['aliases'][$GLOBALS['project_settings']['information']['sys_status']][$alias];
		} else if (isset($GLOBALS['aliases']['GLOBAL'][$alias]) && $GLOBALS['aliases']['GLOBAL'][$alias] != ''){
			return $GLOBALS['aliases']['GLOBAL'][$alias];
		} else {
			return $alias;
		}
	}
}
?>