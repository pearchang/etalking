<?php

$error = array(
 1 => "時間小於12小時",
 2 => "該時段已被預約",
 3 => "用戶非啟用狀態",
 4 => "點數不足",
 5 => "該時段無預約資料",
 6 => "該時段無法設定為可預約",
 7 => "該時段無法取消可預約狀態",
 8 => "該時段無法取消預約"
);

$msg = '';
 
include 'ajax.config.php';

session_start();

if(isset($_GET['type']) && $_GET['type']=='cancel' ){
	
	if(  strtotime($_GET['date'].' '.$_GET['time'].':00:00') - time() < (12 * 3600) ) // 86400 ) // cancel time
		die(json_encode((object) array('code'=>0,'msg'=>'此課程不可取消！')));
	
	$url = etalking_function.'?f=cancel_free_booking&member_id='.$_SESSION['member']['id'].'&date='.$_GET['date'].'&time='.(int)$_GET['time'];

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
	
	$ymdh = str_replace('-','',$_GET['date']).$_GET['time'];
	if($ymdh <= date('YmdH') )
		die(json_encode((object) array('code'=>0,'msg'=>'此課程已開始，無法預約！')));
	
	$url = etalking_function.'?f=register_free_booking&member_id='.$_SESSION['member']['id'].'&date='.$_GET['date'].'&time='.(int)$_GET['time'].'&type='.$_GET['type'];
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
	die(json_encode((object) array('code'=> (int)$data->result ,'msg'=> $msg )));
}
?>