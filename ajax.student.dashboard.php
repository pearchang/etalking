<?php
	include 'config.php';
	
	if(!isset($_SESSION['member']))
		die(json_encode((object) array( 'code'=> 0 )));
	
	$mid = $_SESSION['member']['id'];
	
	$result = array('code'=>1,
					'points'=>0,
					'classes'=>0,
					'overdue'=>0
					);
					
	//剩餘點數
	$member = $dbconn->fetch_one( 'member', array('id'=> $mid ) );
	$result['points'] = sprintf('%.1f',$member['point']);
	
	//已預約課程
	$now = date('YmdH');					
	$sql = "SELECT COUNT(*) FROM course_registration cr, classroom c ";			
	$sql.= "WHERE cr.classroom_id= c.id AND c.`datetime` > $now AND c.status=10 AND c.`type` not in (10,99)  ";
	$sql.= "AND cr.member_id=".$mid." AND cr.status=10 ";
	$stm = $dbconn->query($sql);
	$total = $stm->fetch(PDO::FETCH_NUM);
	$result['classes'] = (int)$total[0];
			
	//即將開始課程
	$now = time();
	$within = CLASSROOM_COUNTDOWN * 60 ;
	$sql = "SELECT COUNT(*) FROM course_registration cr, classroom c ";			
	$sql.= "WHERE cr.classroom_id= c.id AND UNIX_TIMESTAMP(c.open_time) - $now BETWEEN 0 AND $within AND c.status=10 ";
	$sql.= "AND cr.member_id=".$mid." AND cr.status=10 AND c.`type` not in (10,99) ";
	$stm = $dbconn->query($sql);
	$total = $stm->fetch(PDO::FETCH_NUM);
	$result['overdue'] = (int)$total[0];
	
	die(json_encode((object) array( 'dashboard'=> $result )));
?>