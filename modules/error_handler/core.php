<?
use PuRain\Nothing_Framework as Nothing;

require_once 'core/Framework.php';
require_once 'core/Module.php';

/**
 * Displays errors
 * 
 * Config:
 *	log_errors: true/false (if true this module will log errors)
 *	min_log_level: 0-5 (If severity of error is equal or greater, the module will log it)
 *	display_type: html/json/plaintext (sets the output type: 
 *		'html' will use template from template.html file,
 *		'json' will display error in json format,
 *		'plaintext' will display error in format 'Error: ERROR_CODE, ERROR_DESCRIPTION' )
 *
 * Usage:
 *	Nothing::call_module('error_handler', array(SEVERITY, ERROR_CODE, ERROR_DESCRIPTION, MODULE_NAME, [ DONT_LOG_ERROR = false ]));
 *
 * Example:
 *	Nothing::call_module('error_handler', array("4", "SYS_Error_Code_403", "Forbidden. You don't have permission to access this location", "index"));
 */
class error_handler extends Module{
	static $stack;
	static $module_settings;
	public static function MainActivity($params) {
		static::initializing();
		if (isset($params['0']) && isset($params['1'])  && isset($params['2']) && isset($params['3'])) {
			if (static::$module_settings['properties']['log_errors']) {
				if (static::$module_settings['properties']['min_log_level'] <= $params['0']) {
					if (!isset($params['4'])) {
						Nothing::call_module("system", array("log", "Severity:" . $params['0'], $params['2'] . " (Error code: " . $params['1'] . ") in ". $params['3'] . " module;"));
					}
				}
			}
			switch (static::$module_settings['properties']['display_type']) {
					case 'html':
					$template = file_get_contents(__DIR__ . '/template.html');
					$template = str_replace(array("[description]", "[error_code]"), array($params['2'], $params['1']), $template);
					die($template);
					break;
				case 'json':
					die('{ "Error": "' . $params['1'] . '", "description": "'. $params['2'] .'" }');
					break;
				case 'plaintext':
					die('Error: ' . $params['1'] . ', '.$params['2']);
					break;
				default:
					
				}
		} else {
			Nothing::call_module("system", array("Nothing", "system", "log", "Warning", "Called error_handler module without params"));		
		}
	}
	
}
?>