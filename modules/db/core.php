<?
use PuRain\Nothing_Framework as Nothing;

require_once 'core/Framework.php';
require_once 'core/Module.php';

/**
 * Manages json databases
 */
class db extends Module{
	static $stack;
	static $module_settings;
	public static function MainActivity($params) {
		static::initializing();
		
		if (isset($params['0'])) {
			if (method_exists(static::class, $params['0'])) {
				static::write_to_stack(forward_static_call(array(static::class, $params['0']), $params));
			}
			else {
				Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "DB_Error_Code_400", "Bad request, called undefined method", static::class));
			}
		} else {
			Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "API_Error_Code_400", "Bad request, there are missing params", static::class));
		}

	return static::$stack;
	}
	
/**
 * Returns list of databases/tables or table data
 *
 * Usage:
 *	Nothing::call_module('db', array('SHOW', TYPE, [DB_NAME], [TABLE_NAME]));
 *		TYPE:
 *			databases - returns list of databases
 *			database - returns list of tables in selected database
 *			table - returns content of selected database
 *
 * Example:
 *	Nothing::call_module('db', array('SHOW', 'table', 'pages', 'index'));
 */
	public static function SHOW($params) {
		switch ($params['1']) {
				case 'databases':
					return scandir(__DIR__ . "/db");
					break;
				case 'database':
					return scandir(__DIR__ . "/db/".$params['2']);
					break;
				case 'table':
					if (file_exists(__DIR__ . "/db/".$params['2'])) {
						if (file_exists(__DIR__ . "/db/".$params['2']."/".$params['3'].".json")) {
							return file_get_contents(__DIR__ . "/db/".$params['2']."/".$params['3'].".json");
						} else {
							Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "DB_Error_Code_404", "Database ".$params['2']." doesn't exist", static::class));
						}
					}
					break;
				default:
					Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "DB_Error_Code_400", "The 1 parameter is incorrect or missing in SHOW call", static::class));
		}
	}
	
/**
 * Creates database or table
 *
 * Usage:
 *	Nothing::call_module('db', array('CREATE', TYPE, DB_NAME, [TABLE_NAME], [f]));
 *		TYPE:
 *			database - creates database
 *			table - creates table in selected database
 *		If 'f' key specified, it will create database for table, if it doesn`t exists
 *
 * Example:
 *	Nothing::call_module('db', array('CREATE', 'clients', 'users', 'f'));
 */	
	public static function CREATE($params) {
		switch ($params['1']) {
				case 'database':
					mkdir(__DIR__ . "/db/".$params['2'], 0500);
					break;
				case 'table':
					if (file_exists(__DIR__ . "/db/".$params['2'])) {
						if (!file_exists(__DIR__ . "/db/".$params['2']."/".$params['3'].".json")) {
							file_put_contents(__DIR__ . "/db/".$params['2']."/".$params['3'].".json", '');
						} else if (isset($params['4']) && $params['4'] == "f") {
							file_put_contents(__DIR__ . "/db/".$params['2']."/".$params['3'].".json", '');
						} else {
							Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("2", "DB_Error_Code_409", "Conflict. There is an attempt to overwrite old data", static::class));
						}
					} else {
						if (isset($params['4']) && $params['4'] == "f") {
							mkdir(__DIR__ . "/db/".$params['2'], 0500);
							file_put_contents(__DIR__ . "/db/".$params['2']."/".$params['3'].".json", '');
						} else {
							Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("1", "DB_Error_Code_404", "Database ".$params['2']." doesn't exist", static::class));
						}
					}
					break;
				default:
					Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("1", "DB_Error_Code_400", "The 1 parameter is incorrect or missing in CREATE call", static::class));
			}	
	}
/**
 * Deletes database or table
 *
 * Usage:
 *	Nothing::call_module('db', array('DROP', TYPE, DB_NAME, [TABLE_NAME]));
 *		TYPE:
 *			database - deletes database
 *			table - deletes table in selected database
 *
 * Example:
 *	Nothing::call_module('db', array('DROP', 'clients', 'users'));
 */		
	public static function DROP($params) {
		switch ($params['1']) {
				case 'database':
					rmdir(__DIR__ . "/db/".$params['2']);
					break;
				case 'table':
					unlink(__DIR__ . "/db/".$params['2']."/".$params['3'].".json");
					break;
				default:
					Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "DB_Error_Code_400", "The 1 parameter is incorrect or missing in DROP call", static::class));
			}	
	}

/**
 * inserts data into table
 *
 * Usage:
 *	Nothing::call_module('db', array('INSERT', DB_NAME, TABLE_NAME, SECTOR, KEY, VALUE, [f]));
 *		If 'f' key specified, it will create database and table, if it doesn`t exists
 *
 * Example:
 *	Nothing::call_module('db', array('INSERT', 'clients', 'users', 'admin', 'pass', '1f3uhuuq9124'));
 */	
	public static function INSERT($params) {
		if (file_exists(__DIR__ . "/db/".$params['1'])) {
			if (file_exists(__DIR__ . "/db/".$params['1']."/".$params['2'].".json")) {
				$old = json_decode(file_get_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json"), true);
				if ($params['3'] != '') {
					$old = static::array_merge_recursive_distinct($old, array($params['3'] => array($params['4'] => $params['5'])));
				} else {
					$old = static::array_merge_recursive_distinct($old, array($params['4'] => $params['5']));
				}
				file_put_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json", json_encode($old));
			} else {
				if (isset($params['6']) && $params['6'] == "f") {
					if ($params['3'] != '') {
						file_put_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json", json_encode (array($params['3'] => array($params['4'] => $params['5']))));
					} else {
						file_put_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json", json_encode (array($params['4'] => $params['5'])));
					}
				} else {
					Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("1", "DB_Error_Code_404", "Table ".$params['2']." doesn't exist", static::class));
				}
			}
		} else {
			if (isset($params['6']) && $params['6'] == "f") {
				mkdir(__DIR__ . "/db/".$params['1'], 0500);
				if ($params['3'] != '') {
					file_put_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json", json_encode (array($params['3'] => array($params['4'] => $params['5']))));
				} else {
					file_put_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json", json_encode (array($params['4'] => $params['5'])));
				}
			} else {
				Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("1", "DB_Error_Code_404", "Database ".$params['1']." doesn't exist", static::class));
			}
		}
	}
	
/**
 * updates key`s value into table
 *
 * Usage:
 *	Nothing::call_module('db', array('UPDATE', DB_NAME, TABLE_NAME, SECTOR, KEY, NEW_VALUE, [f]));
 *		If 'f' key specified, it will create database and table, if it doesn`t exists
 *		if TABLE_NAME = "", it will update data in key without sector
 *
 * Example:
 *	Nothing::call_module('db', array('UPDATE', 'clients', 'users', 'admin', 'pass', '88h17g7gg711'));
 */	
	public static function UPDATE($params) {
		return static::INSERT($params);
	}

/**
 * returns key`s value from table
 *
 * Usage:
 *	Nothing::call_module('db', array('SELECT', DB_NAME, TABLE_NAME, SECTOR, KEY));
 *	if TABLE_NAME = "", it will get data from key without sector
 *
 * Example:
 *	Nothing::call_module('db', array('SELECT', 'clients', 'users', 'admin', 'pass'));
 */		
	public static function SELECT($params) {
		if (file_exists(__DIR__ . "/db/".$params['1'])) {
			if (file_exists(__DIR__ . "/db/".$params['1']."/".$params['2'].".json")) {
				$db = json_decode(file_get_contents(__DIR__ . "/db/".$params['1']."/".$params['2'].".json"), true);
				if ($params['3'] != '') {
					return $db[$params['3']][$params['4']];
				} else return $db[$params['4']];
			} else {
				Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("1", "DB_Error_Code_404", "Table ".$params['2']." doesn't exist", static::class));
			}
		} else {
			Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("1", "DB_Error_Code_404", "Database ".$params['1']." doesn't exist", static::class));
		}
	}
	
	private static function array_merge_recursive_distinct($array1, $array0) {
		$merged = $array1;

		foreach ( $array0 as $key => &$value ) {
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ) {
				$merged [$key] = static::array_merge_recursive_distinct ( $merged [$key], $value );
			} else {
				$merged [$key] = $value;
			}
		}

		return $merged;
	}
}
?>