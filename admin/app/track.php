<?php
if(!GetParam('search_begin')){
	$_GET['search_begin'] = date('Y-m-d', time()-(86400*14) );
	$_GET['search_end'] = date('Y-m-d');
	$VARS['search_begin'] = GetParam('search_begin');
	$VARS['search_end'] = GetParam('search_end');
	
}
$SEARCH_DATE = array("request_date");
$VARS['search_date'] = true;

$VARS['export_button'] = true;
$EXPORT_FIELDS = ['request_date' => '留單時間',
					'member_name' => '姓名',
					'source' => '來源',
					'track' => 'Track',
					'gender' => '性別',
					'age'	 => '年齡',
					'mobile' => '手機',
					'email' => 'email',
					'status' => '追蹤狀況',
					'cnt' => '撥打次數',
					'contract' => '成交金額'
				];
$EXPORT_FILENAME = '名單成效追蹤.csv';
$EXPORT_CALLBACK = 'fn_callback';

$VARS['gender_text'] = $var_member_gender[$VARS['gender']];

$VARS['add_button'] = false;

$TABLE = 'member';

$FUNC_NAME = '名單成效追蹤';

$SEARCH = true;

$WHERE = " m.request_date!='0000-00-00 00:00:00' ";

$SEARCH_KEYS = array ('first_name', 'last_name', 'member_name', 'mobile', 'source', 'track');

$SORT_BY = 'request_date DESC';

$TRANSLATE = array (

  'status' => array($var_member_history_type),
 

  'gender' => array($var_member_gender, $var_member_gender_color)


);
//$SPECIAL = "status <= 50 AND status > 0";

//TRACK
$filter_track = array();
$sql = "SELECT track FROM member WHERE track!='' GROUP BY track ORDER BY track ASC";
$rs->query($sql);
while($row = $rs->fetch()){
	$filter_track[$row['track']] = $row['track'];
}
$VARS['filter_track'] = array( 0 => 'track' ) + $filter_track;
if(GetParam('track')){
	$WHERE.=" AND m.track='".GetParam('track')."' ";
}

//性別
$VARS['filter_gender'] =  array( 0 => '性別' ) + $var_member_gender;
if(GetParam('gender')){
	$WHERE.=" AND m.gender='".GetParam('gender')."' ";
}

//年齡
$age = array( 0 => '年齡',
			  1 => '18~24',
			  2 => '25~30',
			  3 => '31~35',
			  4 => '41~45',
			  5 => '>45'
			);
$VARS['filter_age'] = $age;
if(GetParam('age')){
	switch(GetParam('age')){
		case 1: $sdate= date('Y')-24; $edate= date('Y')-18; break;
		case 2: $sdate= date('Y')-30; $edate= date('Y')-25; break;
		case 3: $sdate= date('Y')-35; $edate= date('Y')-31; break;
		case 4: $sdate= date('Y')-45; $edate= date('Y')-41; break;
	}
	$filter = GetParam('age')==5 ? "m.birthdate < '".(date('Y')-45)."-01-01' " :
							" m.birthdate BETWEEN '$sdate-01-01' AND '$edate-12-31' ";
	$WHERE.=" AND m.birthdate!='0000-00-00' AND $filter ";
}

//名單效果
$effect = array(0 => '名單效果',
				1 => '有效',
				2 => '無效'
			);
$VARS['filter_effect'] = $effect;
if(GetParam('effect')){
	$in    = GetParam('effect')==2 ? 'IN' : 'NOT IN';
	$type1 = GetParam('effect')==2 ? 'OR'  : 'AND';
	$type2 = GetParam('effect')==2 ? '='  : '!=';
	$WHERE.=" AND ( h.contact_status $in (4,5) $type1 h.type $type2 50 ) ";
}

//追蹤狀態
//contact_status 1已接聽 ,2 非本人 ,3未接 ,4關機 ,5空號 ,6通話中 7 預約 demo 8 釋出
$contact_status = array( 1=>1, 2=>7, 3=>6, 4=>3, 5=>4, 6=>8, 7=>5);
$track_type = array( 0 => '追蹤狀態',
			  1 => '已接聽',
			  2 => '預約 demo',			 
			  3 => '通話中',
			  4 => '未接',
			  5 => '關機',
			  6 => '釋出',
			  7 => '空號',
			);
$VARS['filter_track_type'] = $track_type;
if(GetParam('track_type')){
	$t = GetParam('track_type');
	$WHERE.=" AND h.contact_status='".$contact_status[$t]."' ";
}

//成交與否
$contract = array(0 => '成交與否',
				1 => '是',
				2 => '否'
			);
$VARS['filter_contract'] = $contract;
if(GetParam('contract')){
	$in = GetParam('contract')==1 ? 'IN' : 'NOT IN';
	$WHERE.=" AND m.id $in (SELECT member_id FROM member_contract WHERE status=20 AND deleted=0 GROUP BY member_id ) ";
}

$DATA_SQL = <<<EOT

SELECT m.id, m.request_date, m.member_name, m.gender, m.source, m.track, m.mobile, m.email,
	m.birthdate, h.contact_status, h.content, h.next_time
	FROM member m, member_history h, (SELECT member_id, MAX(id) AS id FROM `member_history` GROUP BY member_id) z
	WHERE m.id = z.member_id AND h.id = z.id AND $WHERE
EOT;

////////////////////////////////////

if(MODE=='pie'){
	
	$table = "FROM member m, member_history h, (SELECT member_id, MAX(id) AS id FROM `member_history` GROUP BY member_id) z
	WHERE m.id = z.member_id AND h.id = z.id AND ";
	
	$pie = array();
	
	$em = GetParam('keyword');
	if(GetParam('keyword') && !empty($em) ){
		
		$search = array();
		
		$kw = "like '%".GetParam('keyword')."%'";		
		
		foreach($SEARCH_KEYS as $col){
			
			$search[] = "m.`$col` $kw ";
		}
		
		$WHERE.= ' AND ('.implode(' OR ',$search).')';
		
	}
	
	$WHERE.= " AND m.request_date BETWEEN  '".$_GET['search_begin']."' AND '".$_GET['search_end']."' ";


	//來源
	$sql = "SELECT m.source, COUNT(*) AS total $table ". $WHERE . ' GROUP BY m.source ORDER BY total ';
	$rs->query($sql);
	$source = array();
	$tt = 0;
	while($row = $rs->fetch()){
		if(empty($row['source'])) $row['source'] = '無';
		$source[] = array( 'item'=> $row['source'], 'total'=> $row['total'] );
		$tt+=$row['total'];
	}
	if($tt>0){
		foreach($source as $key=>$value){
			$percent = sprintf("%.3f",  ($value['total']/$tt ) )*100;
			$source[$key]['item'] = $value['item'].' ('.$percent.'%)';
		}
	}
	$pie['source'] = $source;
	
	//渠道
	$sql = "SELECT m.track, COUNT(*) AS total $table ". $WHERE . ' GROUP BY m.track ORDER BY total ';
	$rs->query($sql);
	$track = array();
	$tt = 0;
	while($row = $rs->fetch()){
		if(empty($row['track'])) $row['track'] = '無';
		$track[] = array( 'item'=> $row['track'], 'total'=> $row['total'] );
		$tt+=$row['total'];
	}
	if($tt>0){
		foreach($track as $key=>$value){
			$percent = sprintf("%.3f",  ($value['total']/$tt ) )*100;
			$track[$key]['item'] = $value['item'].' ('.$percent.'%)';
		}
	}
	$pie['track'] = $track;
	
	//年齡
	$sql = "SELECT DATE_FORMAT(m.birthdate,'%Y') AS birthday, COUNT(*) AS total $table ". $WHERE . ' GROUP BY birthday ORDER BY total ';
	$rs->query($sql);
	$age = array();
	$tt = 0;
	while($row = $rs->fetch()){
		$row['birthday'] = $row['birthday']=='0000' ?  '無' : date('Y') - $row['birthday'];
		$age[] = array( 'item'=> $row['birthday'], 'total'=> $row['total'] );
		$tt+=$row['total'];
	}
	if($tt>0){
		foreach($age as $key=>$value){
			$percent = sprintf("%.3f",  ($value['total']/$tt ) )*100;
			$age[$key]['item'] = $value['item'].' ('.$percent.'%)';
		}
	}
	$pie['age'] = $age;

	//追蹤狀態
	$sql = "SELECT h.contact_status, COUNT(*) AS total $table ". $WHERE . ' GROUP BY h.contact_status HAVING h.contact_status>0 ORDER BY total ';
	$rs->query($sql);
	$tk = array();
	$contact_status = array( 1=>'已接聽',2=>'非本人',3=>'未接',4=>'關機',5=>'空號',6=>'通話中', 7=> '預約demo', 8=> '釋出');
	while($row = $rs->fetch()){
		$tk[] = array( 'item'=> $contact_status[$row['contact_status']].' ('.$row['total'].')', 'total'=> $row['total'] );
	}
	$pie['track_type'] = $tk;	

	$sql = "SELECT COUNT(*) AS total $table ".$WHERE;
	$rs->query($sql);
	$row = $rs->fetch();
	$total = $row['total'];
	

	
	//效果
	$filter = "";
	$sql = "SELECT COUNT(*) AS total $table ". $WHERE . " $filter ";
	$rs->query($sql);
	$row = $rs->fetch();
	$effect = $row['total'];
	if($effect >0){
		$percent = ( $effect/ $total ) * 100;
		$pie['effect'][1] = ceil($percent);
		$pie['effect'][2] = 100-$pie['effect'][1];
	}else{
		$pie['effect'][1] =0;
		$pie['effect'][2] =100;
	}
	
	//成交
	$filter = " AND m.id IN (SELECT member_id FROM member_contract WHERE status=20 AND deleted=0 GROUP BY member_id ) ";
	$sql = "SELECT COUNT(*) AS total $table ". $WHERE . " $filter ";
	$rs->query($sql);
	$row = $rs->fetch();
	$deal = $row['total'];
	if($deal >0){
		$percent = ( $deal/ $total ) * 100;
		$pie['deal'][1] = ceil($percent);
		$pie['deal'][2] = 100-$pie['deal'][1];
	}else{
		$pie['deal'][1] =0;
		$pie['deal'][2] =100;
	}

	
	//性別
	$sql = "SELECT m.gender, COUNT(*) AS total $table ". $WHERE . ' AND gender=1';
	$rs->query($sql);
	$rs->query($sql);
	$row = $rs->fetch();
	$male = $row['total'];


	if($male>0){
		$percent = ( $male/ $total ) * 100;
		$pie['gender'][1] = ceil($percent);
		$pie['gender'][2] = 100-$pie['gender'][1];
	}else{
		$pie['gender'][1] =0;
		$pie['gender'][2] =100;
	}
	
		
	
	echo genView( 'track_pie' , $pie);
	
	exit;

}

////////////////////////////////////

	require_once('func.inc.php');

////////////////////////////////////



function fn_callback($r)

{
	global $TRANSLATE, $rs2;
	
	$mid = $r['id'];
	
//聯絡次數
	$sql = <<<EOT
	SELECT COUNT(*) as cnt FROM member_history
	WHERE member_id = $mid
EOT;

	$rs2->query($sql);
	$log = $rs2->fetch();
	$r['cnt'] = $log['cnt'];
	
//聯絡狀態
	$sql = <<<EOT
	SELECT type FROM member_history
	WHERE member_id = $mid
	ORDER BY id DESC
	LIMIT 1
EOT;

	$rs2->query($sql);
	$log = $rs2->fetch();
	$r['status'] = $log['type'];
	
//成交金額
	$sql = <<<EOT
	SELECT SUM(price) as total FROM member_contract
	WHERE member_id = $mid AND status=20 AND deleted=0
EOT;

	$rs2->query($sql);
	$log = $rs2->fetch();
	$r['contract'] = $log['total'];
	
//年齡
	$r['age'] = $r['birthdate']!='0000-00-00' ? date('Y') - substr($r['birthdate'],0,4) : '';
	
	if( MODE =='export'){
		foreach ($TRANSLATE as $k => $v)
			$r[$k] = $v[0][$r[$k]];	 
	}
	
	$contact_status = array( 1=>'已接聽',2=>'非本人',3=>'未接',4=>'關機',5=>'空號',6=>'通話中', 7=> '預約demo', 8=> '釋出');
	$r['contact_status'] = $contact_status[$r['contact_status']];
	
	
    return $r;

}



?>