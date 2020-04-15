<?
use PuRain\Nothing_Framework as Nothing;

require_once 'core/Framework.php';
require_once 'core/Module.php';

/**
 * Saves data to log files and clears them
 */
class system extends Module{
	static $stack;
	static $module_settings;
	public static function MainActivity($params) {
		static::initializing();
	
		if (isset($params['0'])) {
			if (method_exists(static::class, $params['0'])) {
				static::write_to_stack(forward_static_call(array(static::class, $params['0']), $params));
			}
			else {
					Nothing::call_module('system', array('Nothing', 'system', 'log', 'ERROR', 'Called undefined command in system module '.$params['0']));
			}
		} else {
			Nothing::call_module('system', array('Nothing', 'system', 'log', 'Warning', 'Called system module without params: '.$params['0']));
		}
		return static::$stack;
	}
	
/**
 * Saves data to log file in 'logs' folder in YEAR.MONTH.DAY.txt file
 *
 * Usage:
 *	Nothing::call_module('system', array('log', 'Severety: '. SEVERETY, DESCRIPTION));
 *
 * Example:
 *	Nothing::call_module('system', array('log', 'Severity:' . $SEVERETY, $params['2']));
 */
	public static function log($params) {
		file_put_contents('modules/system/logs/'.date('Y.m.d').'.txt', date('H:i:s').' - ['.$params['1'].'] '.$params['2'].' (ip: '.$_SERVER['REMOTE_ADDR'].')'.PHP_EOL, FILE_APPEND);
	}

/**
 * clears all logs
 *
 * Usage:
 *	Nothing::call_module('system', array('clear_logs'));
 */
	public static function clear_logs($params) {
		array_map('unlink', glob('modules/system/logs/*.txt'));
	}
}
?>