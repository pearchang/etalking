<?php
	if(!isset($_GET['classroom']) || !is_numeric($_GET['classroom']) ){
		die(json_encode((object) array('code'=>2,'msg'=>'Error 1.')));
	}
	
	include 'config.php';
	$webex = enter_webex( $_GET['classroom'],'demo');
	
	if($webex)
		die(json_encode((object) array('code'=>1,'msg'=> '/webex/demo?classroom='.$_GET['classroom'] )));
	else
		die(json_encode((object) array('code'=>0,'msg'=> 'Close!')));
?>