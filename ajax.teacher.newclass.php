<?php
	if( !isset($_GET['type']) ||  !isset($_GET['id']) || !is_numeric($_GET['id'])
		|| ( $_GET['type']!=10 && $_GET['type']!=20 )  )
			die('error!');
			
	include 'config.php';
	session_start();
	
	$sdate = strtotime(date("Y-m-d H:00:00")) + 46800;
	$edate = strtotime(date("Y-m-d H:00:00")) + 86400;

	$sql = "SELECT * FROM classroom ";
	$sql.= "WHERE consultant_id=".$_SESSION['teacher']['id']." AND open_time BETWEEN '".date('Y-m-d H:i:s',$sdate)."' AND '".date('Y-m-d H:i:s',$edate)."' ";
	$sql.= "AND consultant_confirmed=0 AND status=10 AND id=".$_GET['id'];
	$stm = $dbconn->query($sql);
	if($stm->RowCount()!=1)
		die(json_encode((object) array('code'=>0,'msg'=> 'Overdue opration.')));
	
	
	if($_GET['type']==20) $form['consultant_id']=0;
	else $form   = array('consultant_confirmed'=> $_GET['type'], 'mdate' => date('Y-m-d H:i:s'));
	$filter = array( 'id' => $_GET['id'], 'consultant_id' => $_SESSION['teacher']['id'] );
	$rs = $dbconn->update('classroom', $form, $filter);

	if($rs) die(json_encode((object) array('code'=>1 ,'msg'=> 'success' )));		
	else die(json_encode((object) array('code'=>0,'msg'=> 'System Error!')));	

?>