<?
	error_reporting(1);
	define('BASE_DIR',dirname(dirname(__FILE__)).'/');		
	require_once(BASE_DIR.'application.php');
	Application::run();
?>