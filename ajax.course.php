<?php
	include 'config.php';
	
	if(!isset($_GET['course_id']) || !is_numeric($_GET['course_id']))
		die(json_encode((object) array('code'=> 0 ,'msg'=> '缺編號' )));
	
	$filter = "id=".$_GET['course_id']." AND status=10 AND deleted=0 ";
	$course = $dbconn->fetch_one('course', $filter, false);
	if(!$course) die(json_encode((object) array('code'=> 0 ,'msg'=> '無此課程' )));
	
	$filter   = "id=".$course['material_id']." AND status=10 AND deleted=0 ";
	$material = $dbconn->fetch_one('material', $filter, false); 
	if(!$material) die(json_encode((object) array('code'=> 0 ,'msg'=> '無此教材' )));
	
	if($course['type']==40){
		
		$sql = "SELECT open_time, lesson, material_id FROM classroom WHERE course_id=".$course['id']." AND status=10 AND type=40 ORDER BY lesson ASC";
		$rs = $dbconn->query($sql);
		$data = array();
		while($cr = $rs->fetch(PDO::FETCH_ASSOC)){
			$cr['date'] = date("D n/j H:i ~ H:45", strtotime($cr['open_time']) );
			$data[$cr['material_id']] = $cr;
		}
		
		$filter   = "parent=".$material['id']." AND status=10 AND deleted=0 ";
		$lesson = $dbconn->fetch_all( 'material', $filter, 'rank ASC', 'id,title,brief' );
		
		foreach($lesson as $k => $ls){
			$lesson[$k]['date'] = $data[$ls['id']]['date'];
			$lesson[$k]['sn'] = $data[$ls['id']]['lesson'];
			$lesson[$k]['brief'] = nl2br($ls['brief']);
		}
		
	}else{
		$filter = "course_id=".$course['id'];
		$classroom = $dbconn->fetch_one('classroom', $filter, false, 'open_time');
		$date = date('D n/j H:i ~ H:45',strtotime($classroom['open_time']));
	}
	
	$course = array('title'=> $course['course_name'],
					'brief' => nl2br($course['brief'])
				);
				
	$material = array(
					'title' => $material['title'],
					'brief' => nl2br($material['brief']),
					'date'  => $date
				);
	
	die(json_encode((object) array('code'=> 1,'course'=> $course , 'material'=> $material, 'lesson' => $lesson )));
?>