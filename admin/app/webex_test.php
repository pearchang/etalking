<?php

$id = GetParam('id');

$sql = "SELECT id,webex_name FROM webex WHERE status=10 AND type=5 ";
$rs->query($sql);
$webex = array();
if($rs->count>0){
	while($row = $rs->fetch()){
		$webex[$row['id']] = $row['webex_name'];
	}
}
$VARS['webex'] = GenSelect('webex_id', $webex, true);
$VARS['subtype_select'] = GenSelect('subtype', $var_member_history_subtype, true, true);
$type = array(1=>'學員',2=>'顧問');
$VARS['type_select'] = GenSelect('type', $type, false);

switch (MODE)

{
  case 'edit':
  
	$sql = "SELECT * FROM webex_test WHERE id=".$_GET['id'];
	$rs->query($sql);
	$row = $rs->fetch();
	$VARS['data'] = $row;
  break;
	
  case 'save':
	$_POST['test_user'] = $_SESSION['admin_id'];
	$r = $rs->update( 'webex_test', $_GET['id'] , $_POST, 'id' );
	
	$rs->query("SELECT * FROM webex_test WHERE id=".$_GET['id']);
	$row = $rs->fetch();
	if($row['type']==1){
		$h = array('creator'=> $_SESSION['admin_id'],
					'content'=> '預約測試：<br>測試結果：'.$_POST['result'].'<br>是否通過：'.( $row['pass']==1? '是' : '否' ),
					'type'=>80,
					'subtype'=> $_POST['subtype'],
					'member_id'=> $row['target_id']  );
		$rs->insert('member_history',$h);
	}
	
	json_output(true);
	exit;
  break;
  
  case 'autocomplete_member':
	$kw = "like '%".strtolower(GetParam('term'))."%'";
	//$sql = "SELECT id, first_name, last_name, member_name FROM member WHERE LOWER(first_name) $kw OR LOWER(last_name) $kw OR LOWER(member_name) $kw ";
	$sql = "SELECT id, account, member_name FROM member WHERE ( LOWER(account) $kw OR LOWER(first_name) $kw OR LOWER(last_name) $kw OR LOWER(member_name) $kw OR mobile $kw OR tel $kw  ) AND status=10 ";
	$rs->query($sql);
	$list = array();
	if($rs->count>0){
		while($row = $rs->fetch()){
			//$name = !empty($row['member_name']) ? $row['member_name'] : $row['last_name'].$row['last_name'];
			$name = $row['account'].'('.$row['member_name'].')';
			$list[] = array( 'id' => $row['id'], 'label' => $name );
		}
	}
	json_output($list);
  exit;
  break;
  
  
  
  case 'autocomplete_consultant':
	$kw = "like '%".strtolower(GetParam('term'))."%'";
	$sql = "SELECT id, first_name, last_name FROM consultant WHERE ( LOWER(first_name) $kw OR LOWER(last_name) $kw OR chi_name $kw OR tel $kw ) AND status=10 ";
	//$sql = "SELECT id, account FROM consultant WHERE LOWER(account) $kw";	
	$rs->query($sql);
	$list = array();
	if($rs->count>0){
		while($row = $rs->fetch()){
			$name = $row['first_name'].' '.$row['last_name'];
			//$name = $row['account'];
			$list[] = array( 'id' => $row['id'], 'label' => $name );
		}
	}
	json_output($list);
  exit;
  break;
  
  case 'time_option':
		
		$date = GetParam('date');
		$sdate= $date.' 00:00:00';
		$edate= $date.' 23:59:59';		 
		$now = date("Y-m-d H:i:s",time()-1799);//echo $now;
		$sql = "SELECT datetime, qyt FROM webextestconfig WHERE datetime BETWEEN '$sdate' AND '$edate' AND datetime >= '$now' AND qyt>0";
		$rs->query($sql);
		if($rs->count>0){
			$option[]= "<select name=datetime class='ui-select datetime'><option></option>";
			while($row = $rs->fetch()){
				$num = get_booking( $row['datetime'] );
				$row['qyt']-= $num;
				if($row['qyt']>0)
					$option[]="<option value='".$row['datetime']."'>".substr($row['datetime'],11,5).' ('.$row['qyt']."人)</option>";
			}
			$option[]='</select>';
		}
		
		if(!$option OR count($option)<3){
			$option = "<div style='margin-top:7px'>無時段可選擇</div>";
		}else{
			$option = implode("\n",$option);
		}
		
	json_output( array('options'=> $option) );
	exit;
  break;
  
  case 'add':	

 	$v = array();
	$v['open_time']  = $_POST['datetime'];
	$t = strtotime($v['open_time']);
    $v['begin_time'] = date('Y-m-d H:i:00', $t - 3600);
    $v['end_time']   = date('Y-m-d H:i:00', $t + 3600 * 3);	
	$v['status'] = 10;
	$v['date'] = date('Y-m-d',$t);
	$v['time'] = date('H:i',$t);
	$_POST['date'] = date('Y-m-d',$t);
	$_POST['time'] = date('H:i',$t);
	$v['memo'] = MD5( time() );
	
	//檢查學員是否預約過
	if($_POST['type']==1 && $_POST['confirm']==1){
		$sql = "SELECT COUNT(*) as cnt FROM webex_test WHERE `type`=1 AND status=10 AND target_id=".$_POST['target_id'];
		$rs->query($sql);
		$row = $rs->fetch();
		if($row['cnt']>0){
			json_output(array ('result' => 87,'msg'=>'預約失敗, 該學員已預約'));
		}
	}else{
		unset($_POST['confirm']);
	}
	
	//檢查是否還有名額
	$num = get_booking( $_POST['datetime'] );
	$sql = "SELECT qyt FROM webextestconfig WHERE datetime = '".$_POST['datetime']."'";
	$rs->query($sql);
	if(!$rs->count){
		json_output(array ('result' => 2,'msg'=>'預約失敗, 選擇之日期已關閉'));
	}else{
		$row = $rs->fetch();
		if($row['qyt']==0)
			json_output(array ('result' => 3,'msg'=>'預約失敗, 選擇之日期已關閉'));
		elseif($row['qyt']-$num<=0 )
			json_output(array ('result' => 4,'msg'=>'預約失敗, 選擇之時段已滿'));
	}
	
	//檢查帳號是否有效
	if( $_POST['type']==1 ){
		$sql = "SELECT * FROM member WHERE id='".$_POST['target_id']."' AND status=10 ";
	}else{
		$sql = "SELECT * FROM consultant WHERE id='".$_POST['target_id']."' AND status=10 ";
	}
	$rs->query($sql);
	$row = $rs->fetch();
	if(!isset($row['id'])){
		json_output(array ('result' => 5,'msg'=>'預約失敗, 無合法之此受測者帳號'));
	}	
	if(!$_POST['target_id']) $_POST['target_id'] = $row['id'];
	
	$wid = check_webex2( $v['open_time'] );
	if (!$wid)
      json_output(array ('result' => 6,'msg'=>'預約失敗, 無可預約教室'));

	$v['webex_id'] = $wid;
	$rs->insert('classroom_test', $v);
	$cid = $rs->last_id;
	
 	if( $cid && create_test_meeting_room($cid)){
	
		$_POST['classroom_id'] = $cid;		
		$_POST['status'] = 10;
		//$_POST['test_user'] = $_SESSION['admin_id'];
		$rs->insert('webex_test', $_POST );
		
		if($rs->last_id){
			
			$v['name'] = $_POST['target'];
			
			$email = $_POST['type']==1 ? $rs->get_value("member","email",$_POST['target_id'],'id') : 
											$rs->get_value("consultant","email",$_POST['target_id'],'id');
			if( $_POST['type']==2){
				$v['memo'].= "?teacher=1";
				$v['name'] = $row['first_name'].' '.$row['last_name'];
			}else{
				$v['name'] = $row['member_name'];
			}
			if(empty($email)){				
				Message('預約成功,但電子郵件錯誤', false, MSG_OK);
				json_output(array ('result' => 1));
			}else{
				$m = new MailModule();
				$m->template = 'email_webextest';
				$v['time'] = date('H:i', strtotime($v['open_time']) );
				$m->vars = $v;
				$m->addAddress( $email, $v['name'] );
				$m->subject = $EMAIL_SUBJECT_WEBEXTEST;
				$m->send();
				Message('預約成功, 教室安排後將寄發通知信', false, MSG_OK);
				json_output(array ('result' => 1));
			}		
		}
	}
	json_output(array ('result' => 7,'msg'=>'無法建立教室'));

	break;
  

  case 'cancel':

    $sql = "SELECT c.*, w.id AS wid, w.target_id, w.type, w.creator, c.meeting_key FROM classroom_test c, webex_test w WHERE c.id = $id AND c.id = w.classroom_id";

    $rs->query($sql);

    $r = $rs->fetch();

    unset ($v);

    $v['status'] = 20;

    $rs->update('classroom_test', $id, $v);

    $rs->update('webex_test', $r['wid'], $v);
	
	$rs->select('webex', $r['webex_id']);

	$w = $rs->fetch();

	$wx = new WebexAPI;

	$wx->set_auth( $w["account"], $w["password"], WEBEX_SID, WEBEX_PID);

	$wx->set_url(WEBEX_API_URL);

	$result = $wx->dm($r['meeting_key']);
	
	//發信受測者
	if($r['type']==1){	//學員
		$rs->query("SELECT * FROM member WHERE id=".$r['target_id']);
		$member = $rs->fetch();
		$name = $member['member_name'];
		$email = $member['email'];
	}else{	//顧問
		$rs->query("SELECT * FROM consultant WHERE id=".$r['target_id']);
		$consultant = $rs->fetch();
		$name = $consultant['fist_name'].' '.$consultant['last_name'];
		$email = $consultant['email'];
		
	}
							
	$m = new MailModule();
	
	$r['time'] = date('H:i', strtotime($r['open_time']) );
	
	$m->template = 'email_webextest_cancel';
	$m->vars = $r;
	$m->addAddress( $email, $name );
	
	//發信代約人
	$rs->query("SELECT * FROM user WHERE id='".$r['creator']."'");
	$user = $rs->fetch();
	$name = $user['user_name'];
	$email = $user['email'];
	$m->addAddress( $email, $name );
	
	$m->subject = $EMAIL_SUBJECT_WEBEXTEST_CANCEL;
	$m->send();

    json_output(array ('status' => (int)$result));

    break;
	
	
	

  case 'enter':

    $rs->select('classroom_test', $id);

    $c = $rs->fetch();

    unset ($v);

    $v['status2'] = 10; // enter

    $rs->update('classroom_test', $id, $v);

    $rs->select('webex', $c['webex_id']);

    $w = $rs->fetch();

	
    if(!isset($_GET['ST']))

    {

      $_SESSION['ref'] = $_SERVER['HTTP_REFERER'];



      $bu = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");

      header ("Location: https://etalking.webex.com/etalking/p.php?AT=LO&BU=$bu");

    }

    else

    {

      $data['service'] = "user.GetLoginTicket";

      $data['xml_body'] = "";

      $wx = new WebexAPI;

      $wx->set_auth($w["account"], $w["password"], WEBEX_SID, WEBEX_PID);

      $wx->set_url(WEBEX_API_URL);

      $tk = $wx->getTicket();

      $url = urlencode($_SESSION['ref']); //$_SERVER['HTTP_REFERER']);

      unset ($_SESSION['ref']);

      echo <<<EOT

<form method=POST action="https://etalking.webex.com/etalking/p.php">

  <input type="hidden" name="AT" value="LI">

  <input type="hidden" name="TK" value="$tk">

  <input type="hidden" name="WID" value="{$w['account']}">

  <input type="hidden" name="MU" value="https://etalking.webex.com/etalking/m.php?AT=HM&MK={$c['meeting_key']}&BU=$url">

</form>

<script language="javascript">

  document.forms[0].submit();

</script>

EOT;

    }

    exit;

    break;
	
  case "new":
  
	$VARS['search_begin'] = date('Y-m-d');

  break;

  default:
  
	header("Location:/admin/webex_test/new?x");
	exit;
	

	$filter = GetParam('search_begin') ?  "w.`datetime` BETWEEN '".GetParam('search_begin')." 00:00:00' AND '".GetParam('search_end')." 23:59:59' " :  " w.`datetime` >= NOW() ";

	$DATA_SQL = <<<EOT
		SELECT w.*, c.*, w.date as wdate, w.time as wtime, w.type as wtype FROM classroom_test c, webex_test w 
		WHERE w.classroom_id = c.id AND w.status=10 AND $filter
EOT;

	$SORT_BY = "`datetime` ASC";
	require_once('func.inc.php');
    break;

}



////////////////////////////////////

	

////////////////////////////////////
	$VARS['search_date'] = true;
	$VARS['add_button'] = true;
	$VARS['date'] = GetParam('date');
	$VARS['time'] = GetParam('time');
	
function fn_callback($r)
{
  global $rs3;

	$r['creator'] = $rs3->get_value("user","user_name",$r['creator'],'id');
	$r['test_user'] = $rs3->get_value("user","user_name",$r['test_user'],'id');
			
	if($r['wtype']==1){
			$people = $rs3->get_value("member","account",$r['target_id'],'id');
			$people = "<a href='member/edit?x&id=".$r['target_id']."'>".$people."</a>";
	}else{
			$rs3->query("SELECT first_name,last_name FROM  consultant WHERE id=".$r['target_id']);
			//$rs3->query("SELECT account FROM  consultant WHERE id=".$r['target_id']);
			$people = $rs3->fetch();
			$people = $people['first_name'].' '.$people['last_name'];
			//$people = $people['account'];
			$people = "<a href='consultant/edit?x&id=".$r['target_id']."'>".$people."</a>";
	}
	$r['name']= $people;
	if(is_numeric($r['pass']))
		$r['pass']= $r['pass']==1 ? "<font color=green>Yes</font>" : "<font color=red>No</font>";

  return $r;
}

function create_test_meeting_room($classroom_id)

{

  global $rs3;



  $rs3->select('classroom_test', $classroom_id);

  $r = $rs3->fetch();


  if ($r['webex_id'] == 0)

    return false;

  if ($r['status'] != 10 || !empty($r['meeting_key']))

    return true;

  $t = strtotime($r['open_time']) + 3600 * 3; // due time

//  echo $t . time();

  if ($t < time())

  	return true;

  // check type

  $du = 45;



//    $t -= 3600; // 提早一小時

    $du = 105;

    $title = 'TEST';



  // get webex

  $rs3->select('webex', $r['webex_id']);

  $w = $rs3->fetch();

  $pw = makePassword();



  $wx = new WebexAPI;

  $wx->set_auth( $w["account"], $w["password"], WEBEX_SID, WEBEX_PID);

  $wx->set_url(WEBEX_API_URL);

  $k = $wx->sm($w["account"], $pw, $r['open_time'] . ' ' . $title, date('m/d/Y H:i:s', strtotime($r['begin_time'])), $du);

//  echo $k;

  if (!is_numeric($k))

    return false;

  $v['meeting_key'] = $k;

  $v['meeting_pw'] = $pw;

  $rs3->update('classroom_test', $classroom_id, $v);

  return $k;

}

function check_webex2( $opentime )

{

  $webex_type = 5;

  $rs2 = new ResultSet();

  $rs3 = new ResultSet();

  $sql = "SELECT * FROM webex WHERE status = 10 AND `type` = 5";

  $rs2->query($sql);

  $wid = 0;

  while (($r2 = $rs2->fetch()))

  {

    // 確認該時段沒有被佔

    $sql = "SELECT webex_id FROM classroom_test WHERE webex_id = {$r2['id']} AND open_time='$opentime' AND status = 10";

    //echo $sql . '<br>';

    $rs3->query($sql);

    if ($rs3->count == 0) {

      $wid = $r2['id'];

      break;

    }

  }

  return $wid ? $wid : false;

}


function get_booking( $datetime ){
	
	$rs3 = new ResultSet();
	$sql = "SELECT COUNT(*) as cnt FROM webex_test WHERE datetime = '$datetime' AND status=10 ";
	$rs3->query($sql);
	$rs3 = $rs3->fetch();
	return (int)$rs3['cnt'];
}


?>