<?
use PuRain\Nothing_Framework as Nothing;
require_once 'core/Framework.php';
require_once 'core/Module.php';

/**
 * It`s main part of project, connects all modules together to make it work.
 * It contains all functions, which will be used in your project.
 */
class api extends Module{
	static $stack;
	static $module_settings;
	public static function MainActivity($params) {
		static::initializing();
		
		if (isset($params['0'])) {
			if (method_exists(static::class, $params['0'])) {
				static::write_to_stack(forward_static_call(array(static::class, $params['0']), $params));
			}
			else {
				Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "API_Error_Code_400", "Bad request, api method doesn't exist", static::class, false));
			}
		} else {
			Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "API_Error_Code_400", "Bad request, there are missing params", static::class, false));
		}

	return static::$stack;
	}
	
/**
 * returns data from table with PAGE_NAME from 'pages' database
 *
 * Usage:
 *	Nothing::call_module('api', array('get_page_data', PAGE_NAME));
 *
 * Example:
 *	Nothing::call_module('api', array('get_page_data', 'index'));
 */
	public static function get_page_data($params) {
		$respond = Nothing::call_module("db", array("SHOW", "table", 'pages', $params['1']));
		return $respond;
	}
}
?>