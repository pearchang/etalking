<?php
	if(!isset($_GET['classroom']) || !is_numeric($_GET['classroom']) ){
		die(json_encode((object) array('code'=>2,'msg'=>'Error 1.')));
	}
	
	include 'config.php';
	$webex = enter_webex_test( $_GET['classroom']);
	
	$method = isset($_GET['teacher']) ? "teacher_demo" : "demo";
	
	if($webex)
		die(json_encode((object) array('code'=>1,'msg'=> "/webex_test/$method?classroom=".$_GET['classroom'] )));
	else
		die(json_encode((object) array('code'=>0,'msg'=> 'Close!')));
?>