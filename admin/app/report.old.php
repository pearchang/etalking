<?php

$filter = GetParam('filter');

$category = array( 1=>"學員資料", 2=> "學員上課狀況", 7=> "學員點數帳目",3=>"老師基本資料" , 8=>"老師上課時數", 5=>"老師DEMO時數", 4=>"老師薪資報表");
$VARS['category'] = $category;
$VARS['filter'] = $filter ? $filter : 1;
$VARS['cid'] = $VARS['filter'];
$_GET['filter']=$VARS['filter'];
$FILTER = "category";

$TABLE = 'report';
$FUNC_NAME = '統計報表';
$CAN_DELETE = true;
$SORT_BY = 'id DESC';
$SEARCH = false;

if (MODE == 'renew'){
	$v['insert_date'] = date('Y-m-d h:i:s');
	$v['cron_date'] = '0000-00-00 00:00:00';
	$v['creator'] = $_SESSION['admin_id'];
	$rs->update('report',GetParam('id'), $v  );
	if( cronjob( GetParam('id') ) ){
		Message("報表已重新產生", false, MSG_OK);
	}else{
		Message("已加入排程", false, MSG_OK);
	}
	header("Location:/admin/report?filter=".$filter);
	exit;
}else if (MODE == 'add'){
	$_POST['insert_date']= date('Y-m-d h:i:s');
	
}else if (MODE == 'delete'){
	$id = GetParam('id');
	$rs->query("SELECT filename FROM report WHERE id=".$id);
	$r = $rs->fetch();
	
	if(!empty($r['filename'])) @unlink("../report/".$r['filename']);

}

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////


$VARS['filter'] = $VARS['cid'];

function fn_add( $id ){
	if( cronjob( $id ) ){
		Message("報表產生", false, MSG_OK);
	}else{
		Message("已加入排程", false, MSG_OK);
	}
}

function cronjob( $id ){
	
	return false;
	
	$c = curl_init();
	curl_setopt($c,CURLOPT_URL,CRONJOB_URL.'report.php?id='.$id);
	$result = curl_exec($c);
	curl_close($c);
	
	return $result;
	/*
	if(!$result){
		echo curl_errno($c);
	}
	*/
}

?>