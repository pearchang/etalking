<?php

$error = array(
 1 => "時間小於24小時",
 2 => "你已在相同段預約過課程，無法同時段重複預約",
 3 => "用戶非啟用狀態",
 4 => "點數不足",
 5 => "該時段無預約資料",
 6 => "該時段無法設定為可預約",
 7 => "該時段無法取消可預約狀態"
);

$msg = '';
 
include 'ajax.config.php';

session_start();

$t = "elective";

if(isset($_GET['type']) && $_GET['type']=='cancel' ){
	
	$url = etalking_function.'?f=cancel_'.$t.'&member_id='.$_SESSION['member']['id'].'&course_id='.$_GET['course_id'];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);

	if(curl_errno($ch)>0)
		die(json_encode((object) array('code'=>0,'msg'=> curl_errno($ch).'系統發生錯誤')));
	curl_close($ch);
	
	$data = json_decode($data);
	if( $data->result!=1 ){
		$msg = isset($error[ abs($data->result) ]) ? $error[ abs($data->result) ] : '不明錯誤';
	}else{
		$msg = "已取消預約課程";
	}
	//$_SESSION['alert']['booking'] = "已取消預約課程";
	die(json_encode((object) array('code'=> (int)$data->result ,'msg'=> $msg )));	
	
}else{

	$url = etalking_function.'?f=register_'.$t.'&member_id='.$_SESSION['member']['id'].'&course_id='.$_GET['course_id'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);

	if(curl_errno($ch)>0)
		die(json_encode((object) array('code'=>0,'msg'=> curl_errno($ch).'系統發生錯誤')));
	curl_close($ch);
	
	$data = json_decode($data);

	if( $data->result!=1 ){
		$msg = isset($error[ abs($data->result) ]) ? $error[ abs($data->result) ] : '不明錯誤';
	}else{
		$msg = "已成功預約課程";
	}
	die(json_encode((object) array('code'=> 1 ,'msg'=> $msg )));
}
?>