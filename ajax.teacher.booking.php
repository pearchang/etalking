<?php
include 'ajax.config.php';
session_start();	

if(isset($_GET['type']) && $_GET['type']=='cancel' ){
	
	if(  strtotime($_GET['date'].' '.$_GET['time'].':00:00') - time() < 86400 )
		die(json_encode((object) array('code'=>0,'msg'=>'Unable to to cancel within in 24 hours.')));
	
	$url = etalking_function.'?f=cancel_available&consultant_id='.$_SESSION['teacher']['id'].'&date='.$_GET['date'].'&time='.(int)$_GET['time'];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);

	if(curl_errno($ch)>0)
		die(json_encode((object) array('code'=>0,'msg'=> curl_errno($ch).' System Error!')));
	curl_close($ch);
	
	$data = json_decode($data);
	$msg = $data->result ? 'Cancel availablity at '.$_GET['date'].' '.$_GET['time'].':00~'.$_GET['time'].':45 successful.' : 'System Error!';
	
	die(json_encode((object) array('code'=> (int)$data->result ,'msg'=> $msg )));	
	
}else{
	
	$ymdh = str_replace('-','',$_GET['date']).$_GET['time'];
	if($ymdh <= date('YmdH') )
		die(json_encode((object) array('code'=>0,'msg'=>'Unable to book.')));
	
	$url = etalking_function.'?f=set_available&consultant_id='.$_SESSION['teacher']['id'].'&date='.$_GET['date'].'&time='.(int)$_GET['time'];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);

	if(curl_errno($ch)>0)
		die(json_encode((object) array('code'=>0,'msg'=> curl_errno($ch).' System Error!')));
	curl_close($ch);

	$data = json_decode($data);
	$msg = $data->result ? 'Set available at '.$_GET['date'].' '.$_GET['time'].':00~'.$_GET['time'].':45 successful.' : 'System Error! Unable to book.';
	
	die(json_encode((object) array('code'=> (int)$data->result ,'msg'=> $msg )));
}
?>