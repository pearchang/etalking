<?php
	function http404(){
		header('HTTP/1.0 404 Not Found');
		exit;
	}
	
	require_once 'config.php';
	define('ROOT',__DIR__.DS);
	define('BACKEND',false);
	require_once 'lib/app.class.php';
	
	param_check();
	
	//前臺專用
	preg_match_all("/\/(\w+)/i", $_SERVER['REQUEST_URI'] ,$params);
	if($params){
		foreach($params[1] as $key=>$value)
			$params[1][$key]= addslashes($value);
	}
	//end
	
	if(isset($params[1]) && count($params[1])){
		$params = $params[1];
		$controller = $params[0];
		$alias = $params[0];
		$action = isset($params[1]) && !empty($params[1]) ? $params[1] : 'index';
		$param  = isset($params[2]) && !empty($params[2]) ? $params[2] : false;
	}else{
		$controller = 'home';
		$alias = false;
		$action = 'index';
		$param  = false;
	}
	
	$file = "controller/{$controller}.php";
	file_exists( $file ) ?
		include $file : http404();
	
	class_exists( 'Page' ) ?
		$Page = new Page : http404();
	
	if(!method_exists( $Page, $action ))
		http404();
	
	$Page->controller = $controller;
	$Page->action = $action;
	$Page->param = $param;
	$Page->dbconn = $dbconn;
	if(!$Page->table) $Page->table = $controller;

	if(method_exists( $Page, 'init' )) $Page->init();
	
	call_user_func_array(array($Page, $Page->action),array());
	
	$Page->output();
	
	
?>
