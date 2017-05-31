<?php
	include 'config.php';
	
	if(!isset($_SESSION['teacher']))
		die(json_encode((object) array( 'code'=> 0 )));
	
	$mid = $_SESSION['teacher']['id'];
	
	$result = array('code'=>1,
					'newclass'=>0,
					'classes'=>0,
					'demo'=>0,
					'overdue'=>0,
					'report'=>0
					);
					
	//new classes

	$sdate = strtotime(date("Y-m-d H:00:00")) + 46800;
	$edate = strtotime(date("Y-m-d H:00:00")) + 86400;
	$sql = "SELECT COUNT(*) FROM classroom ";
	$sql.= "WHERE consultant_id=".$mid." AND open_time BETWEEN '".date('Y-m-d H:i:s',$sdate)."' AND '".date('Y-m-d H:i:s',$edate)."' ";
	$sql.= "AND consultant_confirmed=0 AND status=10 AND `type`>10 AND `type`!=99 ";			
	$stm = $dbconn->query($sql);
	$rs = $stm->fetch(PDO::FETCH_NUM);
	$result['newclass'] = (int)$rs[0];
	
	//classes
	$sql = "SELECT COUNT(*) FROM classroom ";
	$sql.= "WHERE consultant_id=".$mid." AND open_time > '".date('Y-m-d H:00:00')."' ";
	$sql.= "AND consultant_confirmed =10 AND status=10 AND `type`>10  AND `type`!=99  ";
	$stm = $dbconn->query($sql);
	$rs = $stm->fetch(PDO::FETCH_NUM);
	$result['classes'] = (int)$rs[0];
	
	//demo
	$sql = "SELECT COUNT(*) FROM classroom ";
	$sql.= "WHERE consultant_id=".$mid." AND open_time > '".date('Y-m-d H:00:00')."' ";
	$sql.= "AND status=10 AND `type`=10 ";
	$stm = $dbconn->query($sql);
	$rs = $stm->fetch(PDO::FETCH_NUM);
	$result['demo'] = (int)$rs[0];
	
	//classes 即將開始 不含demo
	$sql = "SELECT COUNT(*) FROM classroom ";
	$sql.= "WHERE consultant_id=".$mid." AND open_time = '".date( 'Y-m-d H:00:00', strtotime(date('Y-m-d H:00:00'))+3600 )."' ";
	$sql.= "AND consultant_confirmed =10 AND status=10 AND `type`>10  AND `type`!=99 ";
	$stm = $dbconn->query($sql);
	$rs = $stm->fetch(PDO::FETCH_NUM);
	$result['overdue'] = (int)$rs[0];
	
	//report
	$_24hr = date('Y-m-d H:i:s', time() - 90000 );
	$sql = "SELECT COUNT(*) FROM classroom c, course_registration cr ";
	$sql.= "WHERE c.id=cr.classroom_id AND c.consultant_id=".$mid." AND attend=10 ";
	$sql.= "AND c.open_time BETWEEN '".$_24hr."' AND '".date('Y-m-d H:i:s')."' ";
	$sql.= "AND c.status=10 AND c.consultant_confirmed =10 AND c.type>10  AND `type`!=99 ";
	$sql.= "AND c.id not in (SELECT classroom_id FROM ques_student GROUP BY classroom_id) ";
	$stm = $dbconn->query($sql);
	$rs = $stm->fetch(PDO::FETCH_NUM);
	$result['report'] = (int)$rs[0];
	
	die(json_encode((object) array( 'dashboard'=> $result )));
?>