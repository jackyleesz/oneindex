<?php
error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('PRC');
define('TIME', time());
define('ROOT', str_replace("\\", "/", dirname(__FILE__)) . '/');

//__autoload方法
function i_autoload($className) {
	if (is_int(strripos($className, '..'))) {
		return;
	}

	$file = ROOT . 'lib/' . $className . '.php';
	if (file_exists($file)) {
		include $file;
	}
}
spl_autoload_register('i_autoload');

/**
 * config('name');
 * config('name@file');
 * config('@file');
 */
!defined('CONFIG_PATH') && define('CONFIG_PATH', ROOT . 'config/');
function config($key) {
	static $configs = array();
	list($key, $file) = explode('@', $key, 2);
	$file = empty($file) ? 'base' : $file;

	$file_name = CONFIG_PATH . $file . '.php';
	//读取配置
	if (empty($configs[$file]) AND file_exists($file_name)) {
		$configs[$file] = @include $file_name;
	}

	if (func_num_args() === 2) {
		$value = func_get_arg(1);
		//写入配置
		if (!empty($key)) {
			$configs[$file] = (array) $configs[$file];
			if (is_null($value)) {
				unset($configs[$file][$key]);
			} else {
				$configs[$file][$key] = $value;
			}

		} else {
			if (is_null($value)) {
				return unlink($file_name);
			} else {
				$configs[$file] = $value;
			}

		}
		file_put_contents($file_name, "<?php return " . var_export($configs[$file], true) . ";", LOCK_EX);
	} else {
		//返回结果
		if (!empty($key)) {
			return $configs[$file][$key];
		}

		return $configs[$file];
	}
}

/**
 * config('name');
 * config('name@file');
 * config('@file');
 */
!defined('CACHE_PATH') && define('CACHE_PATH', ROOT . 'cache/');
function cache($key, $value = null) {
	$file = CACHE_PATH . urlencode($key) . '.php';
	if (is_null($value)) {
		$cache = @include $file;
		return (array)$cache;
	} else {
		file_put_contents($file, "<?php return " . var_export(array(TIME, $value), true) . ";", LOCK_EX);
		return array(TIME, $value);
	}
}


if (!function_exists('db')) {
	function db($table) {
		return db::table($table);
	}
}

!defined('VIEW_PATH') && define('VIEW_PATH', ROOT . 'view/');
if (!function_exists('view')) {
	function view($file, $set = null) {
		return view::load($file, $set = null);
	}
}

if (!function_exists('_')) {
	function _($str) {
		return htmlspecialchars($str);
	}
}

if (!function_exists("fastcgi_finish_request")) {
      function fastcgi_finish_request()  {
      	ob_flush();
      	flush();
      }
}