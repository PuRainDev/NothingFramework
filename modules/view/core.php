<?
use PuRain\Nothing_Framework as Nothing;

require_once 'core/Framework.php';
require_once 'core/Module.php';
require_once 'vendor/autoload.php';

/**
 * Displays your pages with TWIG
 *	this module will activate TWIG and pass to it template from 'templates' folder
 *	with data from api module through Nothing::call_module("api", array("get_page_data", PAGE_NAME))
 *	request.
 * 
 * Config:
 *	default_page: PAGE_NAME (sets the default page)
 *
 * Usage:
 *	Nothing::call_module('view', array(PAGE_NAME));
 *
 * Example:
 *	Nothing::call_module('view', array('index'));
 */
class view extends Module{
	static $stack;
	static $module_settings;
	public static function MainActivity($params) {
		static::initializing();
		
		$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
		$twig = new \Twig\Environment($loader, [
			'cache' => __DIR__ . '/cache',
		]);
		
		if (isset($params['0']) && $params['0']!='Nothing') {
			switch ($params['0']) {
				case 'error':
					static::write_to_stack($twig->render('error.html', ['code' => $params['1'], 'description' => $params['2']]));
					break;
				default:
					if (file_exists("modules/view/templates/".$params['0'].".html")) {
						$data = json_decode(Nothing::call_module("api", array("Nothing", "api", "get_page_data", $params['0'])), true);
						static::write_to_stack($twig->render($params['0'].'.html', $data));
					} else {
						Nothing::call_module($GLOBALS['project_settings']['general']['errors_pass_to'], array("0", "VIEW_Error_Code_404", "The requested page doesn't exist", static::class, false));
					}	
			}	
		} else {
			$data = json_decode(Nothing::call_module("api", array("get_page_data", static::$module_settings['properties']['default_page'])), true);
			static::write_to_stack($twig->render(static::$module_settings['properties']['default_page'].".html",  $data));
		}

	return static::$stack;
	}
}
?>