<?php
	include 'config.php';
	
	if(!isset($_SESSION['member'])) die('Access deny.');
	
	if($_GET['type']=='class'){
		
		$now = date('Y-m-d H:i:s');
		$id = $_POST['id'];
		$member_id = $_SESSION['member']['id'];
		
		foreach($_POST['items'] as $key => $value){			
			$value = $dbconn->quote($value);
			$sql = "INSERT INTO ques_consultant (status,cdate,mdate,classroom_id,member_id,ques_id,content) ";
			$sql.= "VALUES (10,'{$now}','{$now}',{$id},{$member_id},{$key},{$value})";
			$dbconn->query($sql);
		}
		foreach($_POST['checkbox'] as $key => $array){
			$implode = array();
			foreach($array as $value){
				$implode[]=$value;
			}			
			$implode = implode(',',$implode);
			$implode = $dbconn->quote($value);
			$sql = "INSERT INTO ques_consultant (status,cdate,mdate,classroom_id,member_id,ques_id,tag,content) ";
			$sql.= "VALUES (10,'{$now}','{$now}',{$id},{$member_id},{$key},1,{$implode})";
			$dbconn->query($sql);
		}
		
		die(json_encode((object) array('code'=> 1 ,'msg'=> "評鑑完成!" )));
	}	
?>