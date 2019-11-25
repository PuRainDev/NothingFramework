<?php
use PuRain\Nothing_Framework as Nothing;
require_once 'core/Framework.php';

class Module {
	public static function write_to_stack($data) {
		static::$stack .= $data;
	}
	public static function initializing() {
		static::$stack = '';
		static::$module_settings = '';
		static::$module_settings = Nothing::get_module_settings(static::class);
	}
}
?>