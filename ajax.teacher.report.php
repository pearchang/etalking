<?php
	include 'config.php';
	
	if(!isset($_SESSION['teacher'])) die('Access deny.');
	
	if($_GET['type']=='demo'){
		
		$now = date('Y-m-d H:i:s');
		$id = $_POST['id'];
		$member_id = $_POST['member_id'];		
		
		foreach($_POST['items'] as $key => $value){
			
			$value = $dbconn->quote($value);
			$sql = "INSERT INTO ques_demo (status,cdate,mdate,classroom_id,member_id,ques_id,content) ";
			$sql.= "VALUES (10,'{$now}','{$now}',{$id},{$member_id},{$key},{$value})";
			$dbconn->query($sql);
		}
		
		$items = array( $_POST['items'][2] , $_POST['items'][3] , $_POST['items'][4] );
		$avg = $dbconn->query("SELECT SUM(text) FROM ques_item WHERE id in (".implode(',',$items).")");
		$avg = $avg->fetch(PDO::FETCH_NUM);
		$avg = $avg[0]/30;
		$level = $dbconn->fetch_one('level','id='.$_POST['items'][1]);		
		$avg = $level['id']==7 ? $level['begin'] : $avg * ( $level['end'] - $level['begin']) + $level['begin'];
		
		$values = array('level_id'=> $_POST['items'][1], 'grade'=>sprintf('%.1f',$avg) );
		$dbconn->update("member", $values , array('id'=>$member_id) );
		
		die(json_encode((object) array('code'=> 1 ,'msg'=> "Success!" )));
	}
	
	if($_GET['type']=='class'){
		
		$now = date('Y-m-d H:i:s');
		$id = $_POST['id'];
		
		foreach($_POST['items'] as $member_id => $item){
			foreach($item as $key => $value){
				$value = $dbconn->quote($value);
				$sql = "INSERT INTO ques_student (status,cdate,mdate,classroom_id,member_id,ques_id,content) ";
				$sql.= "VALUES (10,'{$now}','{$now}',{$id},{$member_id},{$key},{$value})";
				$dbconn->query($sql);
			}
		}
		die(json_encode((object) array('code'=> 1 ,'msg'=> "Success!" )));
	}	
?>